<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Filterable;

#[Fillable([
    'uuid',
    'code',
    'name',
    'status',
])]
class SalesmanRole extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected array $searchable = ['code', 'name'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanRole $salesmanRole) {
            $salesmanRole->uuid ??= (string) Str::uuid();
        });
    }
}
