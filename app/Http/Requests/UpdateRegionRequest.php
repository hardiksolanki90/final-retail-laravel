<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'countryId' => ['required', 'integer'],
            'regionCode' => ['required', 'string', 'max:191'],
            'regionName' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
