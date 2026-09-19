<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'good_receipt_note_id', 'item_id', 'item_uom_id', 'qty', 'reason_id', 'return_reason_id',
])]
class GoodReceiptNoteDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GoodReceiptNoteDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function goodReceiptNote(): BelongsTo
    {
        return $this->belongsTo(GoodReceiptNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReasonType::class, 'reason_id');
    }

    public function returnReason(): BelongsTo
    {
        return $this->belongsTo(ReasonType::class, 'return_reason_id');
    }
}
