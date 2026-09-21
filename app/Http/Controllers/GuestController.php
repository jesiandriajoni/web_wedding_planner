<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Project;
use App\Services\GuestImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function index(Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $guests = $project->guests()->get();

        // Catering portion calculation: total pax of attending guests * 2
        $attendingPax = $project->guests()->where('rsvp', 'hadir')->sum('pax');
        $cateringPortions = $attendingPax * 2;

        return Inertia::render('Projects/Guests', [
            'project' => $project,
            'guests' => $guests,
            'catering_portions' => $cateringPortions,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'side' => 'required|in:pria,wanita,bersama',
            'rsvp' => 'required|in:pending,hadir,absen',
            'pax' => 'required|integer|min:1',
        ]);

        $project->guests()->create([
            'name' => $request->name,
            'side' => $request->side,
            'rsvp' => $request->rsvp,
            'pax' => $request->pax,
        ]);

        return back();
    }

    public function update(Request $request, Project $project, Guest $guest)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'side' => 'required|in:pria,wanita,bersama',
            'rsvp' => 'required|in:pending,hadir,absen',
            'pax' => 'required|integer|min:1',
        ]);

        $guest->update([
            'name' => $request->name,
            'side' => $request->side,
            'rsvp' => $request->rsvp,
            'pax' => $request->pax,
        ]);

        return back();
    }

    public function destroy(Project $project, Guest $guest)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $guest->delete();

        return back();
    }

    public function downloadTemplate(Request $request, Project $project, GuestImportService $importService)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $format = $request->query('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';

        return $importService->downloadTemplate($format);
    }

    public function import(Request $request, Project $project, GuestImportService $importService)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx) atau CSV untuk diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx) atau .csv.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $result = $importService->import($request->file('file'), $project);

        return back()->with('success', $result['message']);
    }
}
