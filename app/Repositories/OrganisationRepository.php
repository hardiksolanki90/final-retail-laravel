<?php

namespace App\Repositories;

use App\Http\Requests\UpdateOrganisationRequest;
use App\Models\Country;
use App\Models\CountryMaster;
use App\Models\Currency;
use App\Models\CurrencyMaster;
use App\Models\Organisation;
use App\Models\User;
use App\Support\RoleProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganisationRepository
{
    public function current(Request $request): JsonResponse
    {
        $organisation = $this->findCurrent($request->user());

        return response()->json([
            'data' => $organisation ? $this->toResource($organisation) : null,
            'message' => $organisation ? 'Organisation retrieved successfully.' : 'No organisation yet.',
        ]);
    }

    public function details(Request $request): JsonResponse
    {
        $organisation = $this->findCurrent($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $organisation ? $this->toResource($organisation) : null,
            'message' => $organisation ? 'Organisation details retrieved successfully.' : 'No organisation yet.',
        ]);
    }

    public function update(UpdateOrganisationRequest $request): JsonResponse
    {
        $organisation = $this->save($request->user(), $request->validated());

        return response()->json([
            'data' => $this->toResource($organisation),
            'message' => 'Organisation updated successfully.',
        ]);
    }

    protected function findCurrent(User $user): ?Organisation
    {
        if (! $user->organisation_id) {
            return null;
        }

        return Organisation::findOrFail($user->organisation_id);
    }

    /**
     * Create-or-update: registration no longer creates an organisation row,
     * so the first call to this (from the Organisation Details screen)
     * creates it, and links it back onto the user.
     */
    protected function save(User $user, array $data): Organisation
    {
        $organisation = $user->organisation_id
            ? Organisation::findOrFail($user->organisation_id)
            : new Organisation(['uuid' => (string) Str::uuid(), 'reg_software_id' => 1]);

        $organisation->fill([
            'org_name' => $data['org_name'],
            'org_company_id' => $data['org_company_id'],
            'org_tax_id' => $data['org_tax_id'] ?? $organisation->org_tax_id,
            'org_street1' => $data['org_street1'],
            'org_street2' => $data['org_street2'] ?? null,
            'org_city' => $data['org_city'] ?? null,
            'org_state' => $data['org_state'] ?? null,
            'org_country_id' => $organisation->org_country_id ?? 1,
            'org_postal' => $data['org_postal'] ?? null,
            'org_phone' => $data['org_phone'],
            'org_contact_person' => $data['org_contact_person'] ?? null,
            'org_contact_person_number' => $data['org_contact_person_number'] ?? null,
            // NOT NULL with a DB default ('USD') that an explicit null would
            // override — matters now that this can be a brand-new, unsaved
            // Organisation() with no prior org_currency to fall back on.
            'org_currency' => $data['org_currency'] ?? ($organisation->org_currency ?? 'USD'),
            'org_fasical_year' => $data['org_fasical_year'] ?? null,
            'is_batch_enabled' => $data['is_batch_enabled'] ?? false,
            'is_credit_limit_enabled' => $data['is_credit_limit_enabled'] ?? false,
            // NOT NULL columns with no nullable default — soft-default to empty string.
            'gstin_number' => $data['gstin_number'] ?? ($organisation->gstin_number ?? ''),
            'gst_reg_date' => $data['gst_reg_date'] ?? ($organisation->gst_reg_date ?? ''),
        ]);
        $organisation->save();

        if (! $user->organisation_id) {
            $user->organisation_id = $organisation->id;

            $roles = RoleProvisioner::provisionSystemRoles($organisation->id);
            $orgAdminRole = $roles['org-admin'];

            $user->role_id = $orgAdminRole->id;
            $user->save();
            $user->assignRole($orgAdminRole);
        }

        if (isset($data['country_master_id'])) {
            $countryMaster = CountryMaster::findOrFail($data['country_master_id']);

            // One countries row per (organisation, country_master) pair —
            // reselecting the same country reuses it instead of duplicating.
            $country = Country::firstOrNew([
                'organisation_id' => $organisation->id,
                'country_master_id' => $countryMaster->id,
            ]);
            $country->fill($countryMaster->toCountrySnapshot());
            $country->status ??= true;
            $country->save();

            $organisation->org_country_id = $country->id;
            $organisation->save();

            $this->syncCurrencyFromCountry($organisation, $countryMaster);
        }

        return $organisation->fresh();
    }

    /**
     * Ensures the org has a tenant Currency row for its selected country's
     * currency (sourced from the global CurrencyMaster list), marked as the
     * org's default. Skips silently if no matching master exists.
     */
    protected function syncCurrencyFromCountry(Organisation $organisation, CountryMaster $countryMaster): void
    {
        $currencyMaster = CurrencyMaster::where('code', $countryMaster->currency_code)->first();

        if (! $currencyMaster) {
            return;
        }

        $currency = Currency::firstOrNew([
            'organisation_id' => $organisation->id,
            'currency_master_id' => $currencyMaster->id,
        ]);
        $currency->fill([
            'name' => $currencyMaster->name,
            'symbol' => $currencyMaster->symbol,
            'code' => $currencyMaster->code,
            'name_plural' => $currencyMaster->name_plural,
            'symbol_native' => $currencyMaster->symbol_native,
            'decimal_digits' => $currencyMaster->decimal_digits,
            'rounding' => $currencyMaster->rounding,
            'default_currency' => true,
            'format' => $currency->format ?? '1,234,567.89',
        ]);
        $currency->save();

        Currency::where('organisation_id', $organisation->id)
            ->where('id', '!=', $currency->id)
            ->update(['default_currency' => false]);
    }

    /**
     * Called directly by AuthRepository to embed the organisation resource
     * in the login/me response — keep this public with an array return.
     */
    public function toResource(Organisation $org): array
    {
        $country = $org->country;
        $taxProfile = $org->resolveTaxProfile();

        return [
            'id' => $org->id,
            'uuid' => $org->uuid,
            'reg_software_id' => $org->reg_software_id,
            'org_name' => $org->org_name,
            'org_company_id' => $org->org_company_id,
            'org_tax_id' => $org->org_tax_id,
            'org_street1' => $org->org_street1,
            'org_street2' => $org->org_street2,
            'org_city' => $org->org_city,
            'org_state' => $org->org_state,
            'org_country_id' => $org->org_country_id,
            'org_postal' => $org->org_postal,
            'org_phone' => $org->org_phone,
            'org_contact_person' => $org->org_contact_person,
            'org_contact_person_number' => $org->org_contact_person_number,
            'org_currency' => $org->org_currency,
            'org_fasical_year' => $org->org_fasical_year,
            'is_batch_enabled' => (bool) $org->is_batch_enabled,
            'is_credit_limit_enabled' => (bool) $org->is_credit_limit_enabled,
            'org_logo' => $org->org_logo,
            'gstin_number' => $org->gstin_number,
            'gst_reg_date' => $org->gst_reg_date,
            'is_auto_approval_set' => (bool) $org->is_auto_approval_set,
            'org_status' => (bool) $org->org_status,
            'is_trial_period' => (bool) $org->is_trial_period,
            'is_complete' => $this->isComplete($org),
            'country' => $country ? [
                'id' => $country->id,
                'countryMasterId' => $country->country_master_id,
                'name' => $country->name,
                'countryCode' => $country->country_code,
                'dialCode' => $country->dial_code ?? null,
                'currency' => $country->currency ?? null,
                'currencyCode' => $country->currency_code ?? null,
                'currencySymbol' => $country->currency_symbol ?? null,
                'taxProfile' => $taxProfile ? [
                    'taxSystem' => $taxProfile['taxSystem'],
                    'taxName' => $taxProfile['taxName'],
                    'registrationNumberLabel' => $taxProfile['registrationNumberLabel'],
                    'components' => $taxProfile['components'],
                    'jurisdictionLevel' => $taxProfile['jurisdictionLevel'],
                    'calculationNotes' => $taxProfile['calculationNotes'],
                    'taxStatus' => $taxProfile['taxStatus'],
                    'defaultRate' => $taxProfile['defaultRate'],
                ] : null,
            ] : null,
        ];
    }

    /**
     * True once the fields required across the onboarding wizard's steps are
     * actually filled in — registration creates the org shell before any of
     * these are set, so this is what distinguishes "shell" from "onboarded".
     */
    protected function isComplete(Organisation $org): bool
    {
        return trim((string) $org->org_name) !== ''
            && trim((string) $org->org_company_id) !== ''
            && trim((string) $org->org_street1) !== ''
            && trim((string) $org->org_phone) !== '';
    }
}
