<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'decimal_digits' => 'integer',
            'rounding' => 'integer',
        ];
    }
}
