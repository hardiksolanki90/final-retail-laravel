<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'customerTypeName' => ['nullable', 'string', 'max:191'],
            'customerTypeCode' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
