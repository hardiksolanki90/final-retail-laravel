<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'name',
    'code',
])]
class OutletProductCode extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (OutletProductCode $outletProductCode) {
            $outletProductCode->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
