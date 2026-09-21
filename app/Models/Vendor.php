<?php

namespace App\Models;

use App\Events\DashboardUpdated;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['project_id', 'name', 'category', 'contact', 'package_price', 'paid_amount', 'status', 'mou_path'])]
class Vendor extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): HasMany
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
            'status' => $status,
        ]);
    }

    protected static function booted()
    {
        static::saved(function ($vendor) {
            event(new DashboardUpdated($vendor->project));
        });

        static::deleted(function ($vendor) {
            if ($vendor->mou_path) {
                Storage::disk('public')->delete($vendor->mou_path);
            }
            event(new DashboardUpdated($vendor->project));
        });
    }
}
