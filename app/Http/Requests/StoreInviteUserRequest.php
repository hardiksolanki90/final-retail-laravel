<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInviteUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
