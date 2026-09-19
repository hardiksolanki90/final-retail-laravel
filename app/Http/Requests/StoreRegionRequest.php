<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'countryId' => ['nullable', 'integer'],
            'regionCode' => ['required_without:code', 'nullable', 'string', 'max:191'],
            'code' => ['required_without:regionCode', 'nullable', 'string', 'max:191'],
            'regionName' => ['required_without:name', 'nullable', 'string', 'max:191'],
            'name' => ['required_without:regionName', 'nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
