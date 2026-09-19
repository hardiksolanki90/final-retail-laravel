<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryName' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
