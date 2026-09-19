<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countryCode = $this->user()?->organisation?->country?->country_code ?? '';

        $rules = [
            'bankCode' => ['required', 'string', 'max:191'],
            'bankName' => ['required', 'string', 'max:191'],
            'bankAddress' => ['required', 'string', 'max:191'],
            'accountNumber' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
            'iban' => ['nullable', 'string', 'max:191'],
            'swiftCode' => ['nullable', 'string', 'max:191'],
            'ifscCode' => ['nullable', 'string', 'max:191'],
            'routingNumber' => ['nullable', 'string', 'max:191'],
            'sortCode' => ['nullable', 'string', 'max:191'],
            'branchName' => ['nullable', 'string', 'max:191'],
        ];

        if (strtoupper($countryCode) === 'AE') {
            $rules['iban'] = ['required', 'string', 'max:191'];
            $rules['swiftCode'] = ['required', 'string', 'max:191'];
        } elseif (strtoupper($countryCode) === 'IN') {
            $rules['ifscCode'] = ['required', 'string', 'max:191'];
        } elseif (strtoupper($countryCode) === 'US') {
            $rules['routingNumber'] = ['required', 'string', 'max:191'];
        } elseif (strtoupper($countryCode) === 'GB') {
            $rules['sortCode'] = ['required', 'string', 'max:191'];
        }

        return $rules;
    }
}
