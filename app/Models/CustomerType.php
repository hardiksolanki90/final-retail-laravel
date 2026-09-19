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
    'customer_type_code',
    'customer_type_name',
    'status',
])]
class CustomerType extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected array $searchable = ['customer_type_code', 'customer_type_name'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomerType $customerType) {
            $customerType->uuid ??= (string) Str::uuid();
        });
    }
}
