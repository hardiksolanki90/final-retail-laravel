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
    'route_id',
    'region_id',
    'salesman_helper_id',
    'salesman_type_id',
    'salesman_role_id',
    'designation',
    'category_id',
    'salesman_code',
    'employee_code',
    'salesman_supervisor',
    'supervisor_id',
    'asm_id',
    'nsm_id',
    'date_of_joning',
    'is_block',
    'block_start_date',
    'block_end_date',
    'status',
    'profile_image',
    'incentive',
    'current_stage',
    'current_stage_comment',
    'is_lob',
    'printer_config',
    'geo_flag',
])]
class SalesmanInfo extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    // NOTE: search also matches against the linked user's firstname/lastname/email
    // (a relation, not a plain column) — that OR-branch can't be expressed by the
    // generic Filterable::scopeFilter, so SalesmanRepository builds the search
    // clause manually instead of calling ::filter() for the 'search' key.
    protected array $searchable = ['salesman_code', 'employee_code'];
    protected array $filterable = ['route_id', 'salesman_type_id', 'salesman_role_id', 'supervisor_id', 'status'];

    protected function casts(): array
    {
        return [
            'is_block' => 'boolean',
            'status' => 'boolean',
            'is_lob' => 'boolean',
            'date_of_joning' => 'date',
            'block_start_date' => 'date',
            'block_end_date' => 'date',
            'incentive' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesmanInfo $salesmanInfo) {
            $salesmanInfo->uuid ??= (string) Str::uuid();
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

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
