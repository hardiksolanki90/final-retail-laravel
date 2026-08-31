<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'order_id',
    'new_salesman_id',
    'old_salesman_id',
    'old_van_id',
    'new_van_id',
    'login_user_id',
    'reason_id',
    'date',
])]
class DriverAndVanSwaping extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DriverAndVanSwaping $item) {
            $item->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function newSalesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_salesman_id');
    }

    public function oldSalesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_salesman_id');
    }

    public function oldVan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Van::class, 'old_van_id');
    }

    public function newVan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Van::class, 'new_van_id');
    }

    public function loginUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'login_user_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ReasonType::class, 'reason_id');
    }
}
