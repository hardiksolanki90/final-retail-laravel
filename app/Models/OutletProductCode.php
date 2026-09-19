<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\Organisationid;
use App\Traits\Filterable;

#[Fillable([
    'uuid',
    'organisation_id',
    'name',
    'code',
])]
class OutletProductCode extends Model
{
    use HasFactory, Organisationid, Filterable;

    protected array $searchable = ['name', 'code'];
    protected array $filterable = [];

    protected static function booted(): void
    {
        static::creating(function (OutletProductCode $outletProductCode) {
            $outletProductCode->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
