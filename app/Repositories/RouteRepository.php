<?php

namespace App\Repositories;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Route::filter($request->only(['search', 'area_id', 'depot_id', 'status']))
            ->with(['area', 'depot'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Route $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'routes'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = Route::filter($request->only(['search', 'area_id', 'depot_id', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (Route $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'routes'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Route retrieved successfully.',
        ]);
    }

    public function store(StoreRouteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Route::create([
            'area_id' => $data['areaId'],
            'depot_id' => $data['depotId'],
            'route_code' => $data['code'] ?? $data['routeCode'] ?? '',
            'route_name' => $data['name'] ?? $data['routeName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item->fresh(['area', 'depot'])),
            'message' => 'Route created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateRouteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $route = $this->findByUuid($uuid);

        $route->fill([
            'area_id' => $data['areaId'] ?? $route->area_id,
            'depot_id' => $data['depotId'] ?? $route->depot_id,
            'route_code' => $data['code'] ?? $data['routeCode'] ?? $route->route_code,
            'route_name' => $data['name'] ?? $data['routeName'] ?? $route->route_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $route->status,
        ]);
        $route->save();

        return response()->json([
            'data' => $this->toResource($route->fresh(['area', 'depot'])),
            'message' => 'Route updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Route deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Route
    {
        return Route::with(['area', 'depot'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(Route $route): array
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
            'status' => (bool) $route->status,
            'area' => $route->relationLoaded('area') && $route->area
                ? ['id' => $route->area->id, 'uuid' => $route->area->uuid, 'code' => $route->area->area_code, 'name' => $route->area->area_name]
                : null,
            'depot' => $route->relationLoaded('depot') && $route->depot
                ? ['id' => $route->depot->id, 'uuid' => $route->depot->uuid, 'name' => $route->depot->depot_name]
                : null,
        ];
    }

    protected function toSelectOption(Route $route): array
    {
        return [
            'id' => $route->id,
            'uuid' => $route->uuid,
            'code' => $route->route_code,
            'routeName' => $route->route_name,
        ];
    }
}
