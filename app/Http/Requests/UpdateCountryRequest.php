<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'countryMasterId' => ['nullable', 'integer', 'exists:country_masters,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
