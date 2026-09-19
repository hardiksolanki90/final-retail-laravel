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
    'uuid', 'organisation_id', 'load_number', 'depot_id', 'route_id', 'trip_id',
    'trip_number', 'van_id', 'delivery_id', 'order_id', 'salesman_id',
    'storage_location_id', 'warehouse_id', 'load_date', 'load_type', 'load_confirm',
    'status', 'approval_status',
])]
class SalesmanLoad extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    // salesman_id and status are deliberately absent from $filterable: the
    // salesman_id filter value is a salesman UUID that must be resolved to
    // an internal id first, and status accepts either a pending/loaded
    // string (mapped to load_confirm) or a boolean — neither fits the
    // generic pass-through in Filterable::scopeFilter, so
    // SalesmanLoadRepository applies both manually.
    protected array $searchable = ['load_number'];
    protected array $filterable = [];
    protected string $dateRangeColumn = 'load_date';

    protected function casts(): array
    {
        return [
            'load_date' => 'date',
            'trip_number' => 'integer',
            'load_type' => 'integer',
            'load_confirm' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanLoad $load) {
            $load->uuid ??= (string) Str::uuid();
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

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesmanLoadDetail::class);
    }
}
