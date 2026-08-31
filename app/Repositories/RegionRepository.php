<?php

namespace App\Repositories;

use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RegionRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Region
    {
        return Region::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Region
    {
        return Region::create([
            'organisation_id' => $organisationId,
            'country_id' => $data['countryId'],
            'region_code' => $data['regionCode'],
            'region_name' => $data['regionName'],
            'region_status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Region
    {
        $region = $this->findByUuid($uuid, $organisationId);

        $region->fill([
            'country_id' => $data['countryId'] ?? $region->country_id,
            'region_code' => $data['regionCode'] ?? $region->region_code,
            'region_name' => $data['regionName'] ?? $region->region_name,
            'region_status' => array_key_exists('status', $data) ? $data['status'] : $region->region_status,
        ]);
        $region->save();

        return $region->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Region $region): array
    {
        return [
            'id' => $region->id,
            'uuid' => $region->uuid,
            'countryId' => $region->country_id,
            'regionCode' => $region->region_code,
            'regionName' => $region->region_name,
            'status' => (bool) $region->region_status,
            'createdAt' => $region->created_at?->toISOString(),
            'updatedAt' => $region->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Region $region): array
    {
        return [
            'id' => $region->id,
            'uuid' => $region->uuid,
            'regionCode' => $region->region_code,
            'regionName' => $region->region_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Region::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('region_code', 'like', "%{$search}%")
                    ->orWhere('region_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('region_status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
