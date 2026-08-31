<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:191'],
            'name' => ['required', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:191'],
            'manager' => ['nullable', 'string', 'max:191'],
            'isMain' => ['nullable', 'boolean'],
            'locType' => ['nullable', 'integer'],
            'lat' => ['nullable', 'string', 'max:191'],
            'lang' => ['nullable', 'string', 'max:191'],
            'depotId' => ['nullable', 'integer'],
            'routeId' => ['nullable', 'integer'],
            'parentWarehouseId' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
