<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Guest;
use Illuminate\Http\UploadedFile;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Common\Entity\Row;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuestImportService
{
    /**
     * Parse and import guests from an uploaded Excel/CSV file.
     */
    public function import(UploadedFile $file, Project $project): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        $rows = [];
        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->readXlsx($filePath);
        } else {
            $rows = $this->readCsv($filePath);
        }

        if (empty($rows)) {
            return [
                'count' => 0,
                'message' => 'File kosong atau tidak dapat dibaca.',
            ];
        }

        // Detect header columns
        $firstRow = array_map(fn($val) => strtolower(trim((string) $val)), $rows[0]);
        $nameIndex = 0;
        $sideIndex = 1;
        $paxIndex = 2;
        $startIndex = 0;

        // Check if row 0 is header
        $isHeader = false;
        foreach ($firstRow as $idx => $col) {
            if (str_contains($col, 'nama') || str_contains($col, 'name') || str_contains($col, 'tamu')) {
                $nameIndex = $idx;
                $isHeader = true;
            } elseif (str_contains($col, 'pihak') || str_contains($col, 'side') || str_contains($col, 'keluarga')) {
                $sideIndex = $idx;
                $isHeader = true;
            } elseif (str_contains($col, 'pax') || str_contains($col, 'jumlah') || str_contains($col, 'kuota') || str_contains($col, 'total')) {
                $paxIndex = $idx;
                $isHeader = true;
            }
        }

        if ($isHeader) {
            $startIndex = 1;
        }

        $guestsToInsert = [];
        $now = now();

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            $name = isset($row[$nameIndex]) ? trim((string) $row[$nameIndex]) : '';

            // Skip empty rows
            if ($name === '') {
                continue;
            }

            // Parse Side
            $rawSide = isset($row[$sideIndex]) ? strtolower(trim((string) $row[$sideIndex])) : '';
            $side = 'bersama';
            if (str_contains($rawSide, 'pria') || str_contains($rawSide, 'groom') || str_contains($rawSide, 'laki')) {
                $side = 'pria';
            } elseif (str_contains($rawSide, 'wanita') || str_contains($rawSide, 'bride') || str_contains($rawSide, 'perempuan')) {
                $side = 'wanita';
            }

            // Parse Pax
            $rawPax = isset($row[$paxIndex]) ? trim((string) $row[$paxIndex]) : '';
            $pax = (int) preg_replace('/[^0-9]/', '', $rawPax);
            if ($pax < 1) {
                $pax = 2; // Default 2 pax
            }

            $guestsToInsert[] = [
                'project_id' => $project->id,
                'name' => $name,
                'side' => $side,
                'rsvp' => 'pending',
                'pax' => $pax,
                'guest_book_message' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($guestsToInsert)) {
            // Batch insert in chunks of 200 for performance
            foreach (array_chunk($guestsToInsert, 200) as $chunk) {
                Guest::insert($chunk);
            }
        }

        return [
            'count' => count($guestsToInsert),
            'message' => count($guestsToInsert) . ' data tamu berhasil diimpor.',
        ];
    }

    /**
     * Download Excel template.
     */
    public function downloadTemplate(string $format = 'xlsx'): BinaryFileResponse
    {
        $fileName = 'template_daftar_tamu.' . $format;
        $tempPath = tempnam(sys_get_temp_dir(), 'guest_tmpl_') . '.' . $format;

        if ($format === 'xlsx') {
            $writer = new XlsxWriter();
        } else {
            $writer = new CsvWriter();
        }

        $writer->openToFile($tempPath);

        // Header Row
        $writer->addRow(Row::fromValues(['Nama Tamu', 'Pihak (pria / wanita / bersama)', 'Jumlah Pax']));

        // Sample Data Rows
        $writer->addRow(Row::fromValues(['Budi Santoso', 'pria', 2]));
        $writer->addRow(Row::fromValues(['Siti Rahma', 'wanita', 1]));
        $writer->addRow(Row::fromValues(['Keluarga Ahmad Dahlan', 'bersama', 4]));
        $writer->addRow(Row::fromValues(['dr. Hendra Pratama', 'pria', 2]));

        $writer->close();

        return response()->download($tempPath, $fileName, [
            'Content-Type' => $format === 'xlsx'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Read XLSX file rows into array.
     */
    protected function readXlsx(string $filePath): array
    {
        $rows = [];
        $reader = new XlsxReader();
        $reader->open($filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break; // only read first sheet
        }

        $reader->close();
        return $rows;
    }

    /**
     * Read CSV file rows into array.
     */
    protected function readCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Auto detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);

            $delimiter = ',';
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }

            while (($data = fgetcsv($handle, 10000, $delimiter)) !== false) {
                // Remove UTF-8 BOM if on first element
                if (isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }
}
