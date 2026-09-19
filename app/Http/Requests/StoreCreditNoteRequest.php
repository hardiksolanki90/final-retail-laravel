<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customerId' => ['required'],
            'invoiceId' => ['required'],
            'reason' => ['required', 'string'],
            'salesmanId' => ['nullable'],
            'paymentTermId' => ['nullable'],
            'routeId' => ['nullable'],
            'creditNoteNumber' => ['nullable', 'string', 'max:191'],
            'creditNoteDate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.itemId' => ['required'],
            'items.*.uom' => ['nullable'],
            'items.*.itemUomId' => ['nullable'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
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
