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
    'uuid', 'organisation_id', 'source_warehouse_id', 'destination_warehouse_id',
    'grn_number', 'grn_date', 'grn_remark', 'status',
])]
class GoodReceiptNote extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['grn_number'];
    protected array $filterable = ['status'];
    protected string $dateRangeColumn = 'grn_date';

    protected function casts(): array
    {
        return [
            'grn_date' => 'date',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GoodReceiptNote $grn) {
            $grn->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodReceiptNoteDetail::class);
    }
}
