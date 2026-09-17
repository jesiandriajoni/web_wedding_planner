<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('projects.{projectId}', function ($user, $projectId) {
    return \App\Models\Project::find($projectId)
        ?->users()
        ->where('user_id', $user->id)
        ->exists();
});
