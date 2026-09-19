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
    'uuid', 'organisation_id', 'unload_number', 'route_id', 'warehouse_id', 'van_id',
    'salesman_id', 'transaction_date', 'status', 'approval_status',
])]
class SalesmanUnload extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    // salesman_id is deliberately absent from $filterable: the filter value
    // is a salesman UUID that must be resolved to an internal id first, so
    // SalesmanUnloadRepository applies it manually.
    protected array $searchable = ['unload_number'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanUnload $unload) {
            $unload->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesmanUnloadDetail::class);
    }
}
