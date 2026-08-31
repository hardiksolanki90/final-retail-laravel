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
    'country_id',
    'region_code',
    'region_name',
    'region_status',
])]
class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'region_status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Region $region) {
            $region->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
