<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required_without:routeCode', 'nullable', 'string', 'max:50'],
            'routeCode' => ['required_without:code', 'nullable', 'string', 'max:50'],
            'name' => ['required_without:routeName', 'nullable', 'string', 'max:191'],
            'routeName' => ['required_without:name', 'nullable', 'string', 'max:191'],
            'areaId' => ['nullable', 'integer'],
            'depotId' => ['nullable', 'integer'],
            'vanId' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
