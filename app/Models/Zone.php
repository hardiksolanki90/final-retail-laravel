<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\Filterable;

#[Fillable([
    'uuid',
    'zone_code',
    'name',
    'status',
])]
class Zone extends Model
{
    use HasFactory, Filterable;

    protected array $searchable = ['name'];
    protected array $filterable = ['status'];

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
