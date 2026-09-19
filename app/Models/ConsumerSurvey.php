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
    'uuid', 'organisation_id', 'survey_code', 'survey_name', 'customer_id', 'merchandiser_id',
    'date', 'questions', 'status',
])]
class ConsumerSurvey extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['survey_name', 'survey_code'];
    protected array $filterable = ['status'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'questions' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ConsumerSurvey $survey) {
            $survey->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
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
