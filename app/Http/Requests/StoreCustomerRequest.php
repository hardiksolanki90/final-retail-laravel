<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shopName' => ['required', 'string', 'max:191'],
            'firstName' => ['required', 'string', 'max:191'],
            'address' => ['required', 'string', 'max:191'],
            'lastName' => ['nullable', 'string', 'max:191'],
            'email' => [
                'nullable', 'email', 'max:191',
                Rule::unique('customers', 'email')
                    ->where(fn ($query) => $query->where('organisation_id', $this->user()->organisation_id)),
            ],
            'phoneNumber' => ['nullable', 'string', 'max:191'],
            'code' => ['nullable', 'string', 'max:25'],
            'erpCode' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:191'],
            'state' => ['nullable', 'string', 'max:191'],
            'zipcode' => ['nullable', 'string', 'max:191'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'balance' => ['nullable', 'numeric'],
            'creditLimit' => ['nullable', 'numeric'],
            'creditDays' => ['nullable', 'integer'],
            'trnNo' => ['nullable', 'string', 'max:191'],
            'image' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
            'routeId' => ['nullable', 'integer'],
            'salesmanId' => ['nullable', 'integer'],
            'customerTypeId' => ['nullable', 'integer'],
            'customerCategoryId' => ['nullable', 'integer'],
            'customerGroupId' => ['nullable', 'integer'],
            'channelId' => ['nullable', 'integer'],
            'paymentTermId' => ['nullable', 'integer'],
        ];
    }
}
