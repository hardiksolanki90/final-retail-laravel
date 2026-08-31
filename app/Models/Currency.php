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
    'currency_master_id',
    'name',
    'symbol',
    'code',
    'name_plural',
    'symbol_native',
    'decimal_digits',
    'rounding',
    'default_currency',
    'format',
])]
class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'default_currency' => 'boolean',
            'decimal_digits' => 'integer',
            'rounding' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Currency $currency) {
            $currency->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function currencyMaster(): BelongsTo
    {
        return $this->belongsTo(CurrencyMaster::class);
    }
}
