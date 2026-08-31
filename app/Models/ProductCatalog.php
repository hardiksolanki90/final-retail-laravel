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
    'item_id',
    'barcode',
    'net_weight',
    'flawer',
    'shelf_file',
    'ingredients',
    'energy',
    'fat',
    'protein',
    'carbohydrate',
    'calcium',
    'sodium',
    'potassium',
    'crude_fibre',
    'vitamin',
    'image_string',
])]
class ProductCatalog extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'net_weight' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductCatalog $catalog) {
            $catalog->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
