<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function edit(Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Projects/Invitation', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'invitation_template' => 'nullable|string|in:romantic-luxury,minang-traditional,botanical-nature,classic-royal',
            'groom_name' => 'nullable|string|max:255',
            'bride_name' => 'nullable|string|max:255',
            'music_url' => 'nullable|url|max:2000',
            'akad_location' => 'nullable|string|max:255',
            'akad_datetime' => 'nullable|date',
            'akad_maps_url' => 'nullable|url|max:2000',
            'resepsi_location' => 'nullable|string|max:255',
            'resepsi_datetime' => 'nullable|date',
            'resepsi_maps_url' => 'nullable|url|max:2000',
            'existing_photos' => 'nullable|array',
            'existing_photos.*' => 'string',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|max:5120', // 5MB
        ]);

        $old = $project->prewed_photos ?? [];

        // Only keep paths that were already stored (prevents injecting arbitrary paths)
        $kept = array_values(array_intersect($request->input('existing_photos', []), $old));

        // Delete photos the user removed
        $removed = array_values(array_diff($old, $kept));
        if ($removed) {
            Storage::disk('public')->delete($removed);
        }

        $photos = $kept;
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store('invitations/'.$project->id, 'public');
            }
        }
        $photos = array_slice($photos, 0, 10);

        // Generate = publish
        $project->update([
            'invitation_template' => $request->invitation_template ?? 'romantic-luxury',
            'groom_name' => $request->groom_name,
            'bride_name' => $request->bride_name,
            'prewed_photos' => $photos,
            'music_url' => $request->music_url,
            'akad_location' => $request->akad_location,
            'akad_datetime' => $request->akad_datetime,
            'akad_maps_url' => $request->akad_maps_url,
            'resepsi_location' => $request->resepsi_location,
            'resepsi_datetime' => $request->resepsi_datetime,
            'resepsi_maps_url' => $request->resepsi_maps_url,
            'is_published' => true,
        ]);

        return back();
    }
}
