<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Filterable;

#[Fillable([
    'name',
    'code',
    'name_plural',
    'symbol',
    'symbol_native',
    'decimal_digits',
    'rounding',
])]
class CurrencyMaster extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected array $searchable = ['name', 'code'];
    protected array $filterable = [];

    protected function casts(): array
    {
        return [
            'decimal_digits' => 'integer',
            'rounding' => 'integer',
        ];
    }
}
