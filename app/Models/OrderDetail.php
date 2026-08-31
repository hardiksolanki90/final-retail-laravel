<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'order_id', 'item_id', 'item_uom_id', 'original_item_uom_id', 'discount_id',
    'is_free', 'is_item_poi', 'promotion_id', 'reason_id', 'item_qty', 'item_weight',
    'item_price', 'item_gross', 'item_discount_amount', 'item_net', 'item_vat', 'item_excise',
    'item_grand_total', 'delivered_qty', 'open_qty', 'original_item_qty', 'original_item_price',
    'item_vendor_code', 'request_qty', 'order_status', 'picking_status', 'transportation_status',
    'shipment_status', 'invoice_status', 'is_rfgen_sync', 'is_deleted', 'is_picking',
])]
class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_qty' => 'decimal:2',
            'item_weight' => 'decimal:2',
            'item_price' => 'decimal:2',
            'item_gross' => 'decimal:2',
            'item_discount_amount' => 'decimal:2',
            'item_net' => 'decimal:2',
            'item_vat' => 'decimal:2',
            'item_excise' => 'decimal:2',
            'item_grand_total' => 'decimal:2',
            'delivered_qty' => 'decimal:2',
            'open_qty' => 'decimal:2',
            'original_item_qty' => 'decimal:2',
            'original_item_price' => 'decimal:2',
            'request_qty' => 'decimal:2',
            'is_free' => 'boolean',
            'is_item_poi' => 'boolean',
            'is_rfgen_sync' => 'boolean',
            'is_deleted' => 'boolean',
            'is_picking' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
