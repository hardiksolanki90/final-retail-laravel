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
    'user_id',
    'credit_limit_type',
])]
class UserCreditLimit extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = [];
    protected array $filterable = ['user_id', 'credit_limit_type'];

    protected function casts(): array
    {
        return [
            'credit_limit_type' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UserCreditLimit $userCreditLimit) {
            $userCreditLimit->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
