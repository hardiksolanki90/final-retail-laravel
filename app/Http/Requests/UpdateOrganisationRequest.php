<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'org_name' => ['required', 'string', 'max:191'],
            'org_company_id' => ['required', 'string', 'max:191'],
            'org_tax_id' => ['nullable', 'string', 'max:191'],
            'org_street1' => ['required', 'string', 'max:191'],
            'org_street2' => ['nullable', 'string', 'max:191'],
            'org_city' => ['nullable', 'string', 'max:191'],
            'org_state' => ['nullable', 'string', 'max:191'],
            'country_master_id' => ['nullable', 'integer', 'exists:country_masters,id'],
            'org_postal' => ['nullable', 'string', 'max:191'],
            'org_phone' => ['required', 'string', 'max:191'],
            'org_contact_person' => ['nullable', 'string', 'max:191'],
            'org_contact_person_number' => ['nullable', 'string', 'max:191'],
            'org_currency' => ['nullable', 'string', 'max:191'],
            'org_fasical_year' => ['nullable', 'string', 'max:191'],
            'is_batch_enabled' => ['nullable', 'boolean'],
            'is_credit_limit_enabled' => ['nullable', 'boolean'],
            'gstin_number' => ['nullable', 'string', 'max:50'],
            'gst_reg_date' => ['nullable', 'string', 'max:50'],
        ];
    }
}
