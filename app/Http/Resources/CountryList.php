<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'countryMasterId' => $this->country_master_id,
            'name' => $this->name,
            'countryCode' => $this->country_code,
            'dialCode' => $this->dial_code,
            'currency' => $this->currency,
            'currencyCode' => $this->currency_code,
            'currencySymbol' => $this->currency_symbol,
            'status' => (bool) $this->status,
        ];
    }
}
