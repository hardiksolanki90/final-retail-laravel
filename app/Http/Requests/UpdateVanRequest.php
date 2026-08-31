<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vanCode' => ['required', 'string', 'max:25'],
            'plateNumber' => ['required', 'string', 'max:15'],
            'description' => ['required', 'string', 'max:191'],
            'capacity' => ['nullable', 'integer'],
            'areaId' => ['nullable', 'integer'],
            'vanTypeId' => ['required', 'integer'],
            'vanCategoryId' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'reading' => ['nullable', 'integer'],
        ];
    }
}
