<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'deliveryId' => ['nullable'],
            'salesmanId' => ['nullable'],
            'paymentTermId' => ['nullable'],
            'paymentTerms' => ['nullable'],
            'orderTypeId' => ['nullable', 'integer'],
            'orderType' => ['nullable'],
            'invoiceType' => ['nullable'],
            'invoiceTypeId' => ['nullable'],
            'invoiceNumber' => ['nullable', 'string', 'max:191'],
            'invoiceDate' => ['nullable', 'date'],
            'dueDate' => ['nullable', 'date'],
            'invoiceDueDate' => ['nullable', 'date'],
            'depotId' => ['nullable'],
            'routeId' => ['nullable'],
            'warehouseId' => ['nullable'],
            'vanId' => ['nullable'],
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
