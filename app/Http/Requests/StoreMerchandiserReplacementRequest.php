<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchandiserReplacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oldSalesmanId' => ['required', 'integer'],
            'newSalesmanId' => ['required', 'integer'],
            'type' => ['required', 'string', 'max:191'],
            'addedOn' => ['required', 'date'],
        ];
    }
}
