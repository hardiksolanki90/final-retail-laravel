<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'name',
    'no_truck',
    'status',
])]
class Zone extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Zone $zone) {
            $zone->uuid ??= (string) Str::uuid();
        });
    }
}
