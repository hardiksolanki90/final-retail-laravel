<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\Organisationid;
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
    'parent_id',
    'name',
    'node_level',
    'status',
])]
class SalesOrganisation extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['name'];
    protected array $filterable = ['status', 'parent_id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'node_level' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesOrganisation $model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'sales_organisation_id');
    }
}
