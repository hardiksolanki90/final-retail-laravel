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
    'uuid', 'organisation_id', 'survey_code', 'survey_name', 'product_id', 'customer_id',
    'merchandiser_id', 'date', 'appearance', 'aroma', 'taste', 'texture', 'overall_rating',
    'comments', 'status',
])]
class SensorySurvey extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['survey_name', 'survey_code'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'appearance' => 'decimal:1',
            'aroma' => 'decimal:1',
            'taste' => 'decimal:1',
            'texture' => 'decimal:1',
            'overall_rating' => 'decimal:1',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SensorySurvey $survey) {
            $survey->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'product_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }
}
