<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'countryMasterId' => ['required', 'integer', 'exists:country_masters,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
