## What to build
Modul rundown hari-H untuk mengatur jadwal kegiatan linier per menit, menugaskan penanggung jawab (assignee dari WO/keluarga), dan menyediakan fitur export PDF dari backend Laravel.

## Acceptance criteria
- [x] CRUD Rundown Item (Waktu, Aktivitas, Deskripsi, Penanggung Jawab).
- [x] Urutan rundown otomatis terurut secara kronologis berdasarkan kolom waktu.
- [x] Integrasi `barryvdh/laravel-dompdf` untuk mengekspor data rundown aktif menjadi file PDF.
- [x] Tombol "Ekspor PDF" di halaman admin yang mendownload file PDF rundown dengan format bersih dan rapi.

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## PDF Generation & Format Robustness Decisions
1. **Validasi Format Waktu HH:MM (Anti-Format Error)**: Menerapkan aturan regex `/^\d{2}:\d{2}$/` pada parameter input `time` untuk memastikan input waktu selalu dalam format standard militer 24-jam (misal: "08:00", "13:30"). Hal ini memastikan pengurutan kronologis di database berjalan presisi dan bebas anomali.
2. **Dynamic Writable Path Check (Anti-Crash)**: Sebelum melakukan ekspor PDF via Dompdf, sistem melakukan pengecekan dan pembuatan direktori `storage/app/temp` secara dinamis dengan permission `0755` jika folder tersebut belum terbuat. Hal ini menghalau crash penulisan file sementara pada OS server.
3. **Efficiency Stream Output (Anti-Disk Leak)**: File PDF langsung di-stream dan dikirim langsung ke browser klien (menggunakan `$pdf->download(...)`) tanpa melakukan caching permanen pada server disk, yang berpotensi memicu space leaks (kehabisan ruang harddisk server).

