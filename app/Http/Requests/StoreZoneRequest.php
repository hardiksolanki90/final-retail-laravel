<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zoneCode' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
