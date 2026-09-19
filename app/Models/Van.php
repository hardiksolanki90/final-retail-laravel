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
    'uuid',
    'organisation_id',
    'van_code',
    'plate_number',
    'description',
    'capacity',
    'area_id',
    'van_type_id',
    'van_category_id',
    'van_status',
    'reading',
])]
class Van extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['van_code', 'plate_number', 'description'];
    protected array $filterable = ['area_id', 'van_type_id', 'van_status'];

    protected function casts(): array
    {
        return [
            'van_status' => 'boolean',
            'capacity' => 'integer',
            'reading' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Van $van) {
            $van->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function vanType(): BelongsTo
    {
        return $this->belongsTo(VanType::class);
    }

    public function vanCategory(): BelongsTo
    {
        return $this->belongsTo(VanCategory::class);
    }
}
