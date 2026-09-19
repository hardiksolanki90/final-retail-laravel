<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'salesmanId' => ['required'],
            'itemId' => ['required'],
            'divisionId' => ['nullable', 'string'],
            'warehouseId' => ['nullable'],
            'qty' => ['required', 'numeric', 'min:0'],
            'palletType' => ['required', Rule::in(['allocated', 'return'])],
        ];
    }
}
