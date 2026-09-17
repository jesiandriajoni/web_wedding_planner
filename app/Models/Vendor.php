<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'name', 'category', 'contact', 'package_price', 'paid_amount', 'status', 'mou_path'])]
class Vendor extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function updatePaidAmountAndStatus()
    {
        $paid = (float) $this->payments()->sum('amount');
        
        $status = 'pending';
        if ($paid > 0) {
            $status = $paid >= (float) $this->package_price ? 'paid' : 'dp';
        }

        $this->update([
            'paid_amount' => $paid,
            'status' => $status
        ]);
    }

    protected static function booted()
    {
        static::saved(function ($vendor) {
            event(new \App\Events\DashboardUpdated($vendor->project));
        });

        static::deleted(function ($vendor) {
            if ($vendor->mou_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($vendor->mou_path);
            }
            event(new \App\Events\DashboardUpdated($vendor->project));
        });
    }
}
