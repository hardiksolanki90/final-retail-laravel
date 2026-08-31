<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required'],
            'salesmanId' => ['nullable'],
            'paymentTermId' => ['nullable'],
            'paymentTerms' => ['nullable'],
            'orderTypeId' => ['nullable', 'integer'],
            'orderType' => ['nullable'],
            'orderNumber' => ['nullable', 'string', 'max:191'],
            'orderDate' => ['nullable', 'date'],
            'dueDate' => ['nullable', 'date'],
            'deliveryDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'depotId' => ['nullable'],
            'routeId' => ['nullable'],
            'warehouseId' => ['nullable'],
            'status' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.itemId' => ['required'],
            'items.*.itemUomId' => ['nullable'],
            'items.*.uom' => ['nullable'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.unitPrice' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat' => ['nullable', 'numeric', 'min:0'],
            'items.*.excise' => ['nullable', 'numeric', 'min:0'],
            'items.*.uuid' => ['nullable', 'string'],
            'items.*.reasonId' => ['nullable'],
        ];
    }
}
