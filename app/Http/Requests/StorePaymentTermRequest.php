<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'paymentCode' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:191'],
            'numberOfDays' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
