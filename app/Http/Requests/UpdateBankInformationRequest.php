<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bankCode' => ['required', 'string', 'max:191'],
            'bankName' => ['required', 'string', 'max:191'],
            'bankAddress' => ['required', 'string', 'max:191'],
            'accountNumber' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
