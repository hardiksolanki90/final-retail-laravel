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
    'uuid', 'organisation_id', 'journey_name', 'description', 'start_date', 'no_end', 'end_date',
    'start_time', 'end_time', 'journey_plan_base', 'selected_weeks', 'first_day_of_week',
    'enforce_flag', 'merchandiser_id', 'status',
])]
class JourneyPlan extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['journey_name'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'no_end' => 'boolean',
            'enforce_flag' => 'boolean',
            'status' => 'boolean',
            'selected_weeks' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JourneyPlan $plan) {
            $plan->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(JourneyPlanCustomer::class);
    }
}
