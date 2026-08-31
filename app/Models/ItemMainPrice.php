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
    'item_id',
    'item_upc',
    'item_uom_id',
    'item_shipping_uom',
    'is_secondary',
    'stock_keeping_unit',
    'item_price',
    'purchase_order_price',
    'status',
])]
class ItemMainPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_shipping_uom' => 'boolean',
            'is_secondary' => 'boolean',
            'stock_keeping_unit' => 'boolean',
            'item_price' => 'decimal:2',
            'purchase_order_price' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ItemMainPrice $price) {
            $price->uuid ??= (string) Str::uuid();
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
