<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyView extends JsonResource
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
            'currencyMasterId' => $this->currency_master_id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'code' => $this->code,
            'namePlural' => $this->name_plural,
            'symbolNative' => $this->symbol_native,
            'decimalDigits' => $this->decimal_digits,
            'rounding' => $this->rounding,
            'defaultCurrency' => (bool) $this->default_currency,
            'format' => $this->format,
        ];
    }
}
