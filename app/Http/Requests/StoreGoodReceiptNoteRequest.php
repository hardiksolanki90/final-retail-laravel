<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodReceiptNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sourceWarehouseId' => ['nullable'],
            'destinationWarehouseId' => ['nullable'],
            'grnNumber' => ['nullable', 'string', 'max:191'],
            'grnDate' => ['nullable', 'date'],
            'remark' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.itemId' => ['required'],
            'items.*.uom' => ['nullable'],
            'items.*.itemUomId' => ['nullable'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable'],
            'items.*.returnReason' => ['nullable'],
            'items.*.uuid' => ['nullable', 'string'],
        ];
    }
}
