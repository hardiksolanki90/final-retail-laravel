<?php

namespace App\Repositories;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseList;
use App\Http\Resources\WarehouseView;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Warehouse::filter($request->only(['search', 'depot_id', 'route_id', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Warehouse $item) => (new WarehouseList($item))->resolve());

        return response()->json(paginated($paginated, 'warehouses'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Warehouse::filter($request->only(['search', 'depot_id', 'route_id', 'status']))
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Warehouse $item) => $this->toSelectOption($item))->values(),
            'message' => 'Warehouses retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new WarehouseView($item))->resolve(),
            'message' => 'Warehouse retrieved successfully.',
        ]);
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Warehouse::create([
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'address' => $data['address'] ?? null,
            'manager' => $data['manager'] ?? null,
            'manager_phone' => $data['managerPhone'] ?? null,
            'is_main' => $data['isMain'] ?? false,
            'loc_type' => $data['locType'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lang' => $data['lang'] ?? null,
            'depot_id' => $data['depotId'] ?? null,
            'route_id' => $data['routeId'] ?? null,
            'parent_warehouse_id' => $data['parentWarehouseId'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new WarehouseView($item))->resolve(),
            'message' => 'Warehouse created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateWarehouseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $warehouse = $this->findByUuid($uuid);

        $warehouse->fill([
            'code' => $data['code'] ?? $warehouse->code,
            'name' => $data['name'] ?? $warehouse->name,
            'address' => array_key_exists('address', $data) ? $data['address'] : $warehouse->address,
            'manager' => array_key_exists('manager', $data) ? $data['manager'] : $warehouse->manager,
            'manager_phone' => array_key_exists('managerPhone', $data) ? $data['managerPhone'] : $warehouse->manager_phone,
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

        return response()->json([
            'data' => (new WarehouseView($warehouse->fresh()))->resolve(),
            'message' => 'Warehouse updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Warehouse deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Warehouse
    {
        return Warehouse::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Warehouse $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'uuid' => $warehouse->uuid,
            'code' => $warehouse->code,
            'name' => $warehouse->name,
        ];
    }
}

