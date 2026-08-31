<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesmanRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
            'mobile' => ['nullable', 'string', 'max:191'],
            'countryId' => ['nullable', 'integer'],
            'routeId' => ['nullable', 'integer'],
            'salesmanTypeId' => ['nullable', 'integer'],
            'salesmanRoleId' => ['nullable', 'integer'],
            'supervisorId' => ['nullable', 'integer'],
            'employeeCode' => ['nullable', 'string', 'max:50'],
            'salesmanCode' => ['nullable', 'string', 'max:20'],
            'profileImage' => ['nullable', 'string', 'max:191'],
            'designation' => ['nullable', 'string', 'max:191'],
            'joiningDate' => ['nullable', 'date'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
