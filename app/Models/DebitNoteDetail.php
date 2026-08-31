<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'debit_note_id', 'item_id', 'item_condition', 'item_uom_id', 'discount_id',
    'is_free', 'is_item_poi', 'promotion_id', 'item_qty', 'item_price', 'item_gross',
    'item_discount_amount', 'item_net', 'item_vat', 'item_excise', 'item_grand_total',
    'batch_number', 'reason',
])]
class DebitNoteDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_qty' => 'decimal:2',
            'item_price' => 'decimal:2',
            'item_gross' => 'decimal:2',
            'item_discount_amount' => 'decimal:2',
            'item_net' => 'decimal:2',
            'item_vat' => 'decimal:2',
            'item_excise' => 'decimal:2',
            'item_grand_total' => 'decimal:2',
            'is_free' => 'boolean',
            'is_item_poi' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DebitNoteDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
