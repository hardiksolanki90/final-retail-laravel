<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'credit_note_id', 'item_id', 'item_condition', 'item_uom_id',
    'item_qty', 'item_price', 'item_gross', 'item_discount_amount', 'item_net',
    'item_vat', 'item_excise', 'item_grand_total', 'batch_number', 'reason',
])]
class CreditNoteDetail extends Model
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
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CreditNoteDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
