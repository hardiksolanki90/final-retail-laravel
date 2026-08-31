<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'invoice_id', 'item_id', 'item_uom_id', 'van_id', 'discount_id', 'is_free',
    'is_item_poi', 'promotion_id', 'item_qty', 'lower_unit_qty', 'item_price', 'item_gross',
    'item_discount_amount', 'item_net', 'item_vat', 'item_excise', 'item_grand_total',
    'base_price', 'batch_number', 'original_item_qty', 'erp_post_id', 'erp_response_error',
    'is_deleted', 'delv_id', 'deleted_import_data', 'import_date',
])]
class InvoiceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_qty' => 'decimal:2',
            'lower_unit_qty' => 'decimal:2',
            'item_price' => 'decimal:2',
            'item_gross' => 'decimal:2',
            'item_discount_amount' => 'decimal:2',
            'item_net' => 'decimal:2',
            'item_vat' => 'decimal:2',
            'item_excise' => 'decimal:2',
            'item_grand_total' => 'decimal:2',
            'base_price' => 'decimal:2',
            'original_item_qty' => 'decimal:2',
            'is_free' => 'boolean',
            'is_item_poi' => 'boolean',
            'is_deleted' => 'boolean',
            'import_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InvoiceDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }
}
