<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverAndVanSwapingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderId' => ['nullable', 'integer'],
            'newSalesmanId' => ['nullable', 'integer'],
            'oldSalesmanId' => ['nullable', 'integer'],
            'oldVanId' => ['nullable', 'integer'],
            'newVanId' => ['nullable', 'integer'],
            'reasonId' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
        ];
    }
}
