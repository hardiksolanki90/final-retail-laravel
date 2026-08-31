<?php

namespace App\Repositories;

use App\Models\Route;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RouteRepository
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

    public function findByUuid(string $uuid, int $organisationId): Route
    {
        return Route::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Route
    {
        return Route::create([
            'organisation_id' => $organisationId,
            'area_id' => $data['areaId'] ?? 0,
            'depot_id' => $data['depotId'] ?? 0,
            'van_id' => $data['vanId'] ?? null,
            'route_code' => $data['code'] ?? $data['routeCode'] ?? '',
            'route_name' => $data['name'] ?? $data['routeName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Route
    {
        $route = $this->findByUuid($uuid, $organisationId);

        $route->fill([
            'area_id' => $data['areaId'] ?? $route->area_id,
            'depot_id' => $data['depotId'] ?? $route->depot_id,
            'van_id' => array_key_exists('vanId', $data) ? $data['vanId'] : $route->van_id,
            'route_code' => $data['code'] ?? $data['routeCode'] ?? $route->route_code,
            'route_name' => $data['name'] ?? $data['routeName'] ?? $route->route_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $route->status,
        ]);
        $route->save();

        return $route->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Route $route): array
    {
        return [
            'id' => $route->id,
            'uuid' => $route->uuid,
            'code' => $route->route_code,
            'routeCode' => $route->route_code,
            'name' => $route->route_name,
            'routeName' => $route->route_name,
            'areaId' => $route->area_id,
            'depotId' => $route->depot_id,
            'vanId' => $route->van_id,
            'status' => (bool) $route->status,
            'createdAt' => $route->created_at?->toISOString(),
            'updatedAt' => $route->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Route $route): array
    {
        return [
            'id' => $route->id,
            'uuid' => $route->uuid,
            'code' => $route->route_code,
            'routeName' => $route->route_name,
            'name' => $route->route_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Route::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('route_code', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (! empty($filters['depot_id'])) {
            $query->where('depot_id', $filters['depot_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
