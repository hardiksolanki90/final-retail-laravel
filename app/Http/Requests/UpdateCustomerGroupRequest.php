<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'groupName' => ['required', 'string', 'max:191'],
            'groupCode' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:191'],
            'type' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
