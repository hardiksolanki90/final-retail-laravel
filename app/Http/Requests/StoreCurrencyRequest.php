<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currencyMasterId' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:191'],
            'symbol' => ['required', 'string', 'max:10'],
            'code' => ['required', 'string', 'max:191'],
            'namePlural' => ['required', 'string', 'max:191'],
            'symbolNative' => ['required', 'string', 'max:10'],
            'decimalDigits' => ['required', 'integer'],
            'rounding' => ['required', 'integer'],
            'defaultCurrency' => ['nullable', 'boolean'],
            'format' => ['nullable', Rule::in(['1,234,567.89', '1.234.567.89', '1 234 567.89'])],
        ];
    }
}
