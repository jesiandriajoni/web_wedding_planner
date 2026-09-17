<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'vendor_id', 'amount', 'notes'])]
class Payment extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    protected static function booted()
    {
        static::saved(function ($payment) {
            $payment->vendor->updatePaidAmountAndStatus();
        });

        static::deleted(function ($payment) {
            $payment->vendor->updatePaidAmountAndStatus();
        });
    }
}
