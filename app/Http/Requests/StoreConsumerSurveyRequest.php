<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsumerSurveyRequest extends FormRequest
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
            'customerId' => ['required'],
            'merchandiserId' => ['required'],
            'date' => ['required', 'date'],
            'questions' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(['draft', 'completed'])],
        ];
    }
}
