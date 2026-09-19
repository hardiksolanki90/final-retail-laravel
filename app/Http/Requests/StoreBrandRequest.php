<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brandName' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:191'],
            'logoUrl' => ['nullable', 'string', 'max:300'],
            'parentId' => ['nullable', 'integer', 'exists:brands,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
