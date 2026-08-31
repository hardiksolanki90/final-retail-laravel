<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required'],
            'orderId' => ['nullable'],
            'salesmanId' => ['nullable'],
            'paymentTermId' => ['nullable'],
            'paymentTerms' => ['nullable'],
            'deliveryTypeId' => ['nullable'],
            'deliveryType' => ['nullable'],
            'deliveryNumber' => ['nullable', 'string', 'max:191'],
            'deliveryDate' => ['nullable', 'date'],
            'deliveryTime' => ['nullable'],
            'dueDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
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
