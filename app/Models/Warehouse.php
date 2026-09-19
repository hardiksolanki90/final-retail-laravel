<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Organisationid;
use App\Traits\Filterable;

#[Fillable([
    'uuid',
    'organisation_id',
    'code',
    'name',
    'address',
    'manager',
    'manager_phone',
    'is_main',
    'loc_type',
    'lat',
    'lang',
    'depot_id',
    'route_id',
    'parent_warehouse_id',
    'status',
])]
class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['code', 'name'];
    protected array $filterable = ['depot_id', 'route_id', 'status'];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'status' => 'boolean',
            'loc_type' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Warehouse $warehouse) {
            $warehouse->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'parent_warehouse_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'parent_warehouse_id');
    }
}
