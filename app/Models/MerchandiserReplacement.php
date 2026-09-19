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
    'old_salesman_id',
    'new_salesman_id',
    'type',
    'added_on',
])]
class MerchandiserReplacement extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = [];
    protected array $filterable = ['old_salesman_id', 'new_salesman_id'];

    protected function casts(): array
    {
        return [
            'added_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MerchandiserReplacement $item) {
            $item->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function oldSalesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_salesman_id');
    }

    public function newSalesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_salesman_id');
    }
}
