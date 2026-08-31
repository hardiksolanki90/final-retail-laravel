<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required'],
            'salesmanId' => ['required'],
            'invoiceId' => ['nullable'],
            'paymentTermId' => ['nullable'],
            'paymentTerms' => ['nullable'],
            'debitNoteNumber' => ['nullable', 'string', 'max:191'],
            'debitNoteDate' => ['nullable', 'date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'routeId' => ['nullable'],
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
            'items.*.reason' => ['nullable', 'string'],
            'items.*.itemCondition' => ['nullable', 'string'],
            'items.*.batchNumber' => ['nullable', 'string'],
        ];
    }
}
