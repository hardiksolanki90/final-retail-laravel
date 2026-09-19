<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'reg_software_id',
    'org_name',
    'org_company_id',
    'org_tax_id',
    'org_street1',
    'org_street2',
    'org_city',
    'org_state',
    'org_country_id',
    'org_postal',
    'org_phone',
    'org_contact_person',
    'org_contact_person_number',
    'org_currency',
    'org_fasical_year',
    'is_batch_enabled',
    'is_credit_limit_enabled',
    'org_logo',
    'gstin_number',
    'gst_reg_date',
    'is_auto_approval_set',
    'org_status',
    'is_trial_period',
])]
class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_batch_enabled' => 'boolean',
            'is_credit_limit_enabled' => 'boolean',
            'is_auto_approval_set' => 'boolean',
            'org_status' => 'boolean',
            'is_trial_period' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Organisation $organisation) {
            $organisation->uuid ??= (string) Str::uuid();
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'org_country_id');
    }

    /**
     * @return array{taxSystem: ?string, taxEngine: ?string, taxName: ?string, defaultRate: ?string, components: array, registrationNumberLabel: ?string, jurisdictionLevel: array, taxStatus: ?string, calculationNotes: ?string}|null
     */
    public function resolveTaxProfile(): ?array
    {
        $countryMaster = $this->country?->countryMaster;

        return $countryMaster?->tax_system ? $countryMaster->toTaxProfileResource() : null;
    }

    public function salesmen(): HasMany
    {
        return $this->hasMany(SalesmanInfo::class);
    }
}
