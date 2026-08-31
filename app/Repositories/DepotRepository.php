<?php

namespace App\Repositories;

use App\Models\Depot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DepotRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)
            ->with(['region', 'area', 'user'])
            ->orderBy('id')
            ->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Depot
    {
        return Depot::with(['region', 'area', 'user'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Depot
    {
        return Depot::create([
            'organisation_id' => $organisationId,
            'user_id' => $data['userId'] ?? null,
            'region_id' => $data['regionId'],
            'area_id' => $data['areaId'] ?? null,
            'depot_code' => $data['depotCode'],
            'depot_name' => $data['depotName'],
            'depot_manager' => $data['depotManager'],
            'depot_manager_contact' => $data['depotManagerContact'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Depot
    {
        $depot = $this->findByUuid($uuid, $organisationId);

        $depot->fill([
            'user_id' => array_key_exists('userId', $data) ? $data['userId'] : $depot->user_id,
            'region_id' => array_key_exists('regionId', $data) ? $data['regionId'] : $depot->region_id,
            'area_id' => array_key_exists('areaId', $data) ? $data['areaId'] : $depot->area_id,
            'depot_code' => array_key_exists('depotCode', $data) ? $data['depotCode'] : $depot->depot_code,
            'depot_name' => array_key_exists('depotName', $data) ? $data['depotName'] : $depot->depot_name,
            'depot_manager' => array_key_exists('depotManager', $data) ? $data['depotManager'] : $depot->depot_manager,
            'depot_manager_contact' => array_key_exists('depotManagerContact', $data) ? $data['depotManagerContact'] : $depot->depot_manager_contact,
            'status' => array_key_exists('status', $data) ? $data['status'] : $depot->status,
        ]);
        $depot->save();

        return $depot->fresh(['region', 'area', 'user']);
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Depot $depot): array
    {
        return [
            'id' => $depot->id,
            'uuid' => $depot->uuid,
            'userId' => $depot->user_id,
            'regionId' => $depot->region_id,
            'areaId' => $depot->area_id,
            'depotCode' => $depot->depot_code,
            'depotName' => $depot->depot_name,
            'depotManager' => $depot->depot_manager,
            'depotManagerContact' => $depot->depot_manager_contact,
            'status' => (bool) $depot->status,
            'createdAt' => $depot->created_at?->toISOString(),
            'updatedAt' => $depot->updated_at?->toISOString(),
            'region' => $depot->relationLoaded('region') && $depot->region
                ? ['id' => $depot->region->id, 'uuid' => $depot->region->uuid, 'name' => $depot->region->region_name]
                : null,
            'area' => $depot->relationLoaded('area') && $depot->area
                ? ['id' => $depot->area->id, 'uuid' => $depot->area->uuid, 'name' => $depot->area->area_name]
                : null,
        ];
    }

    public function toSelectOption(Depot $depot): array
    {
        return [
            'id' => $depot->id,
            'uuid' => $depot->uuid,
            'depotCode' => $depot->depot_code,
            'depotName' => $depot->depot_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Depot::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('depot_code', 'like', "%{$search}%")
                    ->orWhere('depot_name', 'like', "%{$search}%")
                    ->orWhere('depot_manager', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
