<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'beatCode' => ['required_without_all:beat_code,code', 'nullable', 'string', 'max:50'],
            'beat_code' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:50'],
            'beatName' => ['required_without_all:beat_name,name', 'nullable', 'string', 'max:191'],
            'beat_name' => ['nullable', 'string', 'max:191'],
            'name' => ['nullable', 'string', 'max:191'],
            'areaId' => ['nullable', 'integer'],
            'area_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
