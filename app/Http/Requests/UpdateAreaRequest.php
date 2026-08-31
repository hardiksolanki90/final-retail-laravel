<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'areaName' => ['required_without:name', 'nullable', 'string', 'max:191'],
            'name' => ['required_without:areaName', 'nullable', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer'],
            'nodeLevel' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
