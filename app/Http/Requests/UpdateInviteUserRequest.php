<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:191'],
            'lastname' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($this->route('uuid'), 'uuid')],
            'mobile' => ['nullable', 'string', 'max:20'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
