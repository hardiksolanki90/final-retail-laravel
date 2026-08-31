<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryName' => ['required', 'string', 'max:191'],
            'customerCategoryName' => ['nullable', 'string', 'max:191'],
            'customerCategoryCode' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer'],
            'nodeLevel' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
