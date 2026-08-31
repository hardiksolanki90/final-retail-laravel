<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'rate' => ['required', 'string', 'max:191'],
            'type' => ['required', 'in:CGST,SGST,IGST,UTGST,Cess'],
        ];
    }
}
