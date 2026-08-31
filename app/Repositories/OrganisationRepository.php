<?php

namespace App\Repositories;

use App\Models\Organisation;
use App\Models\User;

class OrganisationRepository
{
    public function current(User $user): Organisation
    {
        return Organisation::findOrFail($user->organisation_id);
    }

    public function update(User $user, array $data): Organisation
    {
        $organisation = Organisation::findOrFail($user->organisation_id);

        $organisation->fill([
            'org_name' => $data['org_name'],
            'org_company_id' => $data['org_company_id'],
            'org_tax_id' => $data['org_tax_id'] ?? $organisation->org_tax_id,
            'org_street1' => $data['org_street1'],
            'org_street2' => $data['org_street2'] ?? null,
            'org_city' => $data['org_city'] ?? null,
            'org_state' => $data['org_state'] ?? null,
            'org_country_id' => isset($data['org_country_id']) ? (int) $data['org_country_id'] : $organisation->org_country_id,
            'org_postal' => $data['org_postal'] ?? null,
            'org_phone' => $data['org_phone'],
            'org_contact_person' => $data['org_contact_person'] ?? null,
            'org_contact_person_number' => $data['org_contact_person_number'] ?? null,
            'org_currency' => $data['org_currency'] ?? $organisation->org_currency,
            'org_fasical_year' => $data['org_fasical_year'] ?? null,
            'is_batch_enabled' => $data['is_batch_enabled'] ?? false,
            'is_credit_limit_enabled' => $data['is_credit_limit_enabled'] ?? false,
            // NOT NULL columns with no nullable default — soft-default to empty string.
            'gstin_number' => $data['gstin_number'] ?? ($organisation->gstin_number ?? ''),
            'gst_reg_date' => $data['gst_reg_date'] ?? ($organisation->gst_reg_date ?? ''),
        ]);
        $organisation->save();

        return $organisation->fresh();
    }
}
