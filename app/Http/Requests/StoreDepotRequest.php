<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => ['nullable', 'integer'],
            'regionId' => ['required', 'integer'],
            'areaId' => ['nullable', 'integer'],
            'depotCode' => ['required', 'string', 'max:20'],
            'depotName' => ['required', 'string', 'max:100'],
            'depotManager' => ['required', 'string', 'max:191'],
            'depotManagerContact' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
