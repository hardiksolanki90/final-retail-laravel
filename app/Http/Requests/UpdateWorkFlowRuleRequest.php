<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkFlowRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'module' => ['required', 'string', Rule::in(StoreWorkFlowRuleRequest::MODULES)],
            'description' => ['nullable', 'string'],
            'eventTrigger' => ['required', 'string', Rule::in(StoreWorkFlowRuleRequest::EVENT_TRIGGERS)],
            'status' => ['nullable', 'boolean'],
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*.roleId' => ['required', 'integer', 'exists:roles,id'],
            'approvers.*.userId' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'approvers.required' => 'Please add at least one approver.',
            'approvers.min' => 'Please add at least one approver.',
        ];
    }
}
