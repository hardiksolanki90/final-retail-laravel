<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:191'],
            'name' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
