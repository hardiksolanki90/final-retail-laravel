<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryMaster extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'jurisdiction_level' => 'array',
            'rate_range' => 'array',
            'components' => 'array',
            'default_rate' => 'decimal:2',
            'tax_verified_at' => 'date',
        ];
    }

    /**
     * Snapshot fields copied onto an organisation's `countries` row when it
     * selects this country — countries keeps its own copy rather than
     * joining live, so a later edit to the master doesn't retroactively
     * change what an org already selected.
     */
    public function toCountrySnapshot(): array
    {
        return [
            'country_master_id' => $this->id,
            'name' => $this->name,
            'country_code' => $this->country_code,
            'dial_code' => $this->dial_code,
            'currency' => $this->currency,
            'currency_code' => $this->currency_code,
            'currency_symbol' => $this->currency_symbol,
        ];
    }

    public function toTaxProfileResource(): array
    {
        return [
            'taxSystem' => $this->tax_system,
            'taxEngine' => $this->tax_engine,
            'taxName' => $this->tax_name,
            'defaultRate' => $this->default_rate,
            'components' => $this->components ?? [],
            'registrationNumberLabel' => $this->registration_number_label,
            'jurisdictionLevel' => $this->jurisdiction_level ?? [],
            'taxStatus' => $this->tax_status,
            'calculationNotes' => $this->calculation_notes,
        ];
    }
}
