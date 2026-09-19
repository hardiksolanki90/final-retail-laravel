<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'journey_plan_id', 'day_of_week', 'sequence', 'customer_id', 'msl_perform',
    'start_time', 'end_time',
])]
class JourneyPlanCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'msl_perform' => 'boolean',
            'sequence' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JourneyPlanCustomer $row) {
            $row->uuid ??= (string) Str::uuid();
        });
    }

    public function journeyPlan(): BelongsTo
    {
        return $this->belongsTo(JourneyPlan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
