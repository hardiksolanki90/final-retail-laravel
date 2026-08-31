<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'item_major_category_id',
    'item_group_id',
    'brand_id',
    'channel_id',
    'is_product_catalog',
    'is_promotional',
    'item_code',
    'erp_code',
    'item_name',
    'item_description',
    'item_barcode',
    'item_weight',
    'item_shelf_life',
    'volume',
    'lower_unit_item_upc',
    'lower_unit_uom_id',
    'lower_unit_item_price',
    'lower_unit_purchase_order_price',
    'item_shipping_uom',
    'is_tax_apply',
    'item_vat_percentage',
    'is_item_excise',
    'item_excise',
    'item_excise_uom_id',
    'new_lunch',
    'start_date',
    'end_date',
    'current_stage',
    'current_stage_comment',
    'item_image',
    'stock_keeping_unit',
    'status',
])]
class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_product_catalog' => 'boolean',
            'is_promotional' => 'boolean',
            'item_weight' => 'decimal:2',
            'volume' => 'decimal:2',
            'lower_unit_item_price' => 'decimal:2',
            'lower_unit_purchase_order_price' => 'decimal:2',
            'item_shipping_uom' => 'boolean',
            'is_tax_apply' => 'boolean',
            'item_vat_percentage' => 'decimal:2',
            'is_item_excise' => 'boolean',
            'item_excise' => 'decimal:2',
            'new_lunch' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'stock_keeping_unit' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Item $item) {
            $item->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function mainPrices(): HasMany
    {
        return $this->hasMany(ItemMainPrice::class);
    }

    public function productCatalog(): HasOne
    {
        return $this->hasOne(ProductCatalog::class);
    }
}
