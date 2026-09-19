<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'salesman_unload_id', 'item_id', 'item_uom_id', 'unload_qty', 'unload_type', 'reason_id',
])]
class SalesmanUnloadDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'unload_qty' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanUnloadDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function salesmanUnload(): BelongsTo
    {
        return $this->belongsTo(SalesmanUnload::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReasonType::class, 'reason_id');
    }
}
