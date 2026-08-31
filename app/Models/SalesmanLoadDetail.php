<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'salesman_load_id', 'route_id', 'depot_id', 'item_id', 'salesman_id',
    'storage_location_id', 'warehouse_id', 'van_id', 'dat_id', 'load_date', 'change_date',
    'item_uom', 'load_qty', 'lower_qty', 'ctn_qty', 'requested_qty',
    'requested_item_uom_id', 'is_exported',
])]
class SalesmanLoadDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'load_date' => 'date',
            'change_date' => 'date',
            'lower_qty' => 'decimal:2',
            'ctn_qty' => 'decimal:2',
            'requested_qty' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanLoadDetail $detail) {
            $detail->uuid ??= (string) Str::uuid();
        });
    }

    public function salesmanLoad(): BelongsTo
    {
        return $this->belongsTo(SalesmanLoad::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
