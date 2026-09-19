<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesmanUnloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unloadNumber' => ['nullable', 'string', 'max:191'],
            'routeId' => ['nullable'],
            'warehouseId' => ['nullable'],
            'vanId' => ['nullable'],
            'salesmanId' => ['required'],
            'transactionDate' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.itemId' => ['required'],
            'items.*.uom' => ['nullable'],
            'items.*.itemUomId' => ['nullable'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unloadType' => ['nullable', 'in:fresh,damage,expired'],
            'items.*.reasonId' => ['nullable'],
        ];
    }
}
