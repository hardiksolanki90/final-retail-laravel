<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required_without:salesOrganisationName', 'nullable', 'string', 'max:191'],
            'salesOrganisationName' => ['required_without:name', 'nullable', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'nodeLevel' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
