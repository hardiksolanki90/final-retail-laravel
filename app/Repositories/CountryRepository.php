<?php

namespace App\Repositories;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CountryRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Country
    {
        return Country::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Country
    {
        return Country::create([
            'organisation_id' => $organisationId,
            'name' => $data['name'] ?? '',
            'country_code' => $data['countryCode'] ?? '',
            'dial_code' => $data['dialCode'] ?? null,
            'currency' => $data['currency'] ?? '',
            'currency_code' => $data['currencyCode'] ?? null,
            'currency_symbol' => $data['currencySymbol'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Country
    {
        $country = $this->findByUuid($uuid, $organisationId);

        $country->fill([
            'name' => $data['name'] ?? $country->name,
            'country_code' => $data['countryCode'] ?? $country->country_code,
            'dial_code' => array_key_exists('dialCode', $data) ? $data['dialCode'] : $country->dial_code,
            'currency' => $data['currency'] ?? $country->currency,
            'currency_code' => array_key_exists('currencyCode', $data) ? $data['currencyCode'] : $country->currency_code,
            'currency_symbol' => $data['currencySymbol'] ?? $country->currency_symbol,
            'status' => array_key_exists('status', $data) ? $data['status'] : $country->status,
        ]);
        $country->save();

        return $country->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Country $country): array
    {
        return [
            'id' => $country->id,
            'uuid' => $country->uuid,
            'name' => $country->name,
            'countryCode' => $country->country_code,
            'dialCode' => $country->dial_code,
            'currency' => $country->currency,
            'currencyCode' => $country->currency_code,
            'currencySymbol' => $country->currency_symbol,
            'status' => (bool) $country->status,
            'createdAt' => $country->created_at?->toISOString(),
            'updatedAt' => $country->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Country $country): array
    {
        return [
            'id' => $country->id,
            'uuid' => $country->uuid,
            'name' => $country->name,
            'countryCode' => $country->country_code,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Country::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%")
                    ->orWhere('currency_code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
