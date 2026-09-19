<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSensorySurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surveyCode' => ['nullable', 'string', 'max:191'],
            'surveyName' => ['required', 'string', 'max:191'],
            'productId' => ['required'],
            'customerId' => ['required'],
            'merchandiserId' => ['required'],
            'date' => ['required', 'date'],
            'appearance' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'aroma' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'taste' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'texture' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'overallRating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'comments' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'completed'])],
        ];
    }
}
