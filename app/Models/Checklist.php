<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'title', 'status', 'assigned_to'])]
class Checklist extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected static function booted()
    {
        static::saved(function ($checklist) {
            event(new \App\Events\DashboardUpdated($checklist->project));
        });

        static::deleted(function ($checklist) {
            event(new \App\Events\DashboardUpdated($checklist->project));
        });
    }
}
