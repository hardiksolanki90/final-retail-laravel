<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesmanLoadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loadCode' => ['nullable', 'string', 'max:191'],
            'loadNumber' => ['nullable', 'string', 'max:191'],
            'salesmanId' => ['required'],
            'vanId' => ['nullable'],
            'warehouseId' => ['nullable'],
            'depotId' => ['nullable'],
            'routeId' => ['nullable'],
            'orderId' => ['nullable'],
            'deliveryId' => ['nullable'],
            'loadDate' => ['nullable', 'date'],
            'loadConfirm' => ['nullable', 'boolean'],
            'status' => ['nullable'],
            'active' => ['nullable', 'boolean'],
            'tripNumber' => ['nullable', 'integer'],
            'items' => ['nullable', 'array'],
            'items.*.itemId' => ['required'],
            'items.*.uom' => ['nullable', 'string'],
            'items.*.itemUom' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.loadQty' => ['nullable'],
            'items.*.uuid' => ['nullable', 'string'],
            'items.*.lowerQty' => ['nullable', 'numeric'],
            'items.*.ctnQty' => ['nullable', 'numeric'],
            'items.*.requestedQty' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            if ($status === null || $status === '') {
                return;
            }

            if (is_bool($status) || is_numeric($status)) {
                return;
            }

            if (is_string($status) && in_array(strtolower($status), ['pending', 'loaded'], true)) {
                return;
            }

            $validator->errors()->add('status', 'The status must be pending, loaded, or a boolean.');
        });
    }
}
