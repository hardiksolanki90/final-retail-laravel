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
    'name',
    'parent_id',
    'node_level',
    'status',
])]
class VanCategory extends Model
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
        static::creating(function (VanCategory $vanCategory) {
            $vanCategory->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VanCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(VanCategory::class, 'parent_id');
    }
}
