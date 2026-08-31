<?php

namespace App\Repositories;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WarehouseRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Warehouse
    {
        return Warehouse::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Warehouse
    {
        return Warehouse::create([
            'organisation_id' => $organisationId,
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'address' => $data['address'] ?? null,
            'manager' => $data['manager'] ?? null,
            'is_main' => $data['isMain'] ?? false,
            'loc_type' => $data['locType'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lang' => $data['lang'] ?? null,
            'depot_id' => $data['depotId'] ?? null,
            'route_id' => $data['routeId'] ?? null,
            'parent_warehouse_id' => $data['parentWarehouseId'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Warehouse
    {
        $warehouse = $this->findByUuid($uuid, $organisationId);

        $warehouse->fill([
            'code' => $data['code'] ?? $warehouse->code,
            'name' => $data['name'] ?? $warehouse->name,
            'address' => array_key_exists('address', $data) ? $data['address'] : $warehouse->address,
            'manager' => array_key_exists('manager', $data) ? $data['manager'] : $warehouse->manager,
            'is_main' => array_key_exists('isMain', $data) ? $data['isMain'] : $warehouse->is_main,
            'loc_type' => array_key_exists('locType', $data) ? $data['locType'] : $warehouse->loc_type,
            'lat' => array_key_exists('lat', $data) ? $data['lat'] : $warehouse->lat,
            'lang' => array_key_exists('lang', $data) ? $data['lang'] : $warehouse->lang,
            'depot_id' => array_key_exists('depotId', $data) ? $data['depotId'] : $warehouse->depot_id,
            'route_id' => array_key_exists('routeId', $data) ? $data['routeId'] : $warehouse->route_id,
            'parent_warehouse_id' => array_key_exists('parentWarehouseId', $data) ? $data['parentWarehouseId'] : $warehouse->parent_warehouse_id,
            'status' => array_key_exists('status', $data) ? $data['status'] : $warehouse->status,
        ]);
        $warehouse->save();

        return $warehouse->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Warehouse $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'uuid' => $warehouse->uuid,
            'code' => $warehouse->code,
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'manager' => $warehouse->manager,
            'isMain' => (bool) $warehouse->is_main,
            'locType' => $warehouse->loc_type,
            'lat' => $warehouse->lat,
            'lang' => $warehouse->lang,
            'depotId' => $warehouse->depot_id,
            'routeId' => $warehouse->route_id,
            'parentWarehouseId' => $warehouse->parent_warehouse_id,
            'status' => (bool) $warehouse->status,
            'createdAt' => $warehouse->created_at?->toISOString(),
            'updatedAt' => $warehouse->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Warehouse $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'uuid' => $warehouse->uuid,
            'code' => $warehouse->code,
            'name' => $warehouse->name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Warehouse::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['depot_id'])) {
            $query->where('depot_id', $filters['depot_id']);
        }

        if (! empty($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
