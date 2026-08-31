<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'name',
    'payment_code',
    'number_of_days',
    'status',
])]
class PaymentTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'number_of_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentTerm $paymentTerm) {
            $paymentTerm->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
