<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Organisationid;
use App\Traits\Filterable;

#[Fillable([
    'uuid', 'organisation_id', 'date', 'salesman_id', 'item_id', 'division_id', 'warehouse_id',
    'qty', 'pallet_type', 'status',
])]
class Pallet extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = [];
    protected array $filterable = [];
    protected string $dateRangeColumn = 'date';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'qty' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pallet $pallet) {
            $pallet->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
