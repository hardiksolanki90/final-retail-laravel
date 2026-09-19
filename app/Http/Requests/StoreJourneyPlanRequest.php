<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJourneyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'journeyName' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'startDate' => ['required', 'date'],
            'noEnd' => ['nullable', 'boolean'],
            'endDate' => ['nullable', 'date'],
            'startTime' => ['nullable'],
            'endTime' => ['nullable'],
            'journeyPlanBase' => ['nullable', 'in:day_wise,week_wise'],
            'selectedWeeks' => ['nullable', 'array'],
            'firstDayOfWeek' => ['nullable', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'enforceFlag' => ['nullable', 'boolean'],
            'merchandiserId' => ['required'],
            'status' => ['nullable', 'boolean'],
            'dayCustomers' => ['nullable', 'array'],
            'dayCustomers.*' => ['array'],
            'dayCustomers.*.*.customerId' => ['required'],
            'dayCustomers.*.*.sequence' => ['nullable', 'numeric'],
            'dayCustomers.*.*.mslPerform' => ['nullable', 'boolean'],
            'dayCustomers.*.*.startTime' => ['nullable'],
            'dayCustomers.*.*.endTime' => ['nullable'],
        ];
    }
}
