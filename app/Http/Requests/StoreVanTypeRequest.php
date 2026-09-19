<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVanTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer', 'exists:van_types,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
