<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicInvitationController extends Controller
{
    public function show($slug)
    {
        $project = Project::where('slug', $slug)->where('is_published', true)->firstOrFail();

        // Load public rsvp list (only guests with guest book messages)
        $guestBook = $project->guests()
            ->whereNotNull('guest_book_message')
            ->select('id', 'project_id', 'name', 'guest_book_message', 'rsvp', 'updated_at')
            ->get();

        // Load public rundown (optional, if we want to show it on invitation)
        $rundown = $project->rundowns()
            ->select('id', 'project_id', 'time', 'activity', 'description', 'assigned_to')
            ->orderBy('time')
            ->get();

        // Secure whitelist of guest names for public dropdown
        $allGuests = $project->guests()
            ->select('id', 'name')
            ->get();

        return Inertia::render('PublicInvitation', [
            'project' => [
                'name' => $project->name,
                'slug' => $project->slug,
                'wedding_date' => $project->wedding_date,
                'groom_name' => $project->groom_name,
                'bride_name' => $project->bride_name,
                'invitation_template' => $project->invitation_template ?? 'romantic-luxury',
                'prewed_photos' => $project->prewed_photos ?? [],
                'music_url' => $project->music_url,
                'akad_location' => $project->akad_location,
                'akad_datetime' => $project->akad_datetime,
                'akad_maps_url' => $project->akad_maps_url,
                'resepsi_location' => $project->resepsi_location,
                'resepsi_datetime' => $project->resepsi_datetime,
                'resepsi_maps_url' => $project->resepsi_maps_url,
                'guest_book' => $guestBook,
                'rundown' => $rundown,
                'all_guests' => $allGuests,
            ],
        ]);
    }

    public function rsvp(Request $request, $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'rsvp' => 'required|in:hadir,absen',
            'pax' => 'required|integer|min:1|max:10',
            'guest_book_message' => 'nullable|string|max:1000',
        ]);

        $guest = $project->guests()->findOrFail($request->guest_id);

        // Strict whitelist assignment: do not fill side or name from public request payload
        $guest->update([
            'rsvp' => $request->rsvp,
            'pax' => $request->pax,
            'guest_book_message' => $request->guest_book_message,
        ]);

        return back();
    }
}
