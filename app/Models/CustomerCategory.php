<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'customer_category_code',
    'parent_id',
    'node_level',
    'customer_category_name',
    'status',
])]
class CustomerCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'node_level' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomerCategory $customerCategory) {
            $customerCategory->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CustomerCategory::class, 'parent_id');
    }
}
