<?php

namespace App\Models;

use App\Events\DashboardUpdated;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'wedding_date', 'total_budget',
    'groom_name', 'bride_name',
    'is_published', 'invitation_template', 'prewed_photos', 'music_url',
    'akad_location', 'akad_datetime', 'akad_maps_url',
    'resepsi_location', 'resepsi_datetime', 'resepsi_maps_url',
])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'prewed_photos' => 'array',
            'akad_datetime' => 'datetime',
            'resepsi_datetime' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function rundowns(): HasMany
    {
        return $this->hasMany(Rundown::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function seserahanItems(): HasMany
    {
        return $this->hasMany(SeserahanItem::class);
    }

    protected static function booted()
    {
        static::saved(function ($project) {
            event(new DashboardUpdated($project));
        });
    }
}
