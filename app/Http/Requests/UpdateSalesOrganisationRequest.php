<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'salesOrganisationName' => ['sometimes', 'nullable', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'nodeLevel' => ['nullable', 'integer'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
