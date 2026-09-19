<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Filterable;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Extends Spatie's own Role model rather than replacing it — keeps every
 * existing `App\Models\Role` import in the app working unchanged, while
 * getting Spatie's Teams-aware role/permission machinery for free.
 * Mass-assignment is governed by Spatie's own `$guarded = []` (set in its
 * constructor), not a #[Fillable] attribute — every column here, including
 * the app-specific ones added in the migration (uuid/code/description/
 * status/organisation_id), is fillable via create()/fill().
 */
class Role extends SpatieRole
{
    use HasFactory, SoftDeletes, Filterable;

    protected array $searchable = ['code', 'name'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            $role->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }
}
