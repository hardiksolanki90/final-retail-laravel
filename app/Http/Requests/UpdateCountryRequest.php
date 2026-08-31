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
            'name' => ['required', 'string', 'max:191'],
            'countryCode' => ['required', 'string', 'max:10'],
            'dialCode' => ['nullable', 'string', 'max:10'],
            'currency' => ['required', 'string', 'max:50'],
            'currencyCode' => ['nullable', 'string', 'max:10'],
            'currencySymbol' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
