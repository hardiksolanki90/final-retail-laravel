<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkSalesmanActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'block', 'unblock'])],
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['string'],
        ];
    }
}
