<?php

namespace App\Repositories;

use App\Http\Requests\StoreDepotRequest;
use App\Http\Requests\UpdateDepotRequest;
use App\Http\Resources\DepotList;
use App\Http\Resources\DepotView;
use App\Models\Depot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepotRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Depot::filter($request->only(['search', 'region_id', 'area_id', 'status']))
            ->with(['region', 'area'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Depot $item) => (new DepotList($item))->resolve());

        return response()->json(paginated($paginated, 'depots'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Depot::filter($request->only(['search', 'region_id', 'area_id', 'status']))
            ->with(['region', 'area', 'user'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Depot $item) => (new DepotView($item))->resolve())->values(),
            'message' => 'Depots retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new DepotView($item))->resolve(),
            'message' => 'Depot retrieved successfully.',
        ]);
    }

    public function store(StoreDepotRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Depot::create([
            'user_id' => $data['userId'] ?? null,
            'region_id' => $data['regionId'],
            'area_id' => $data['areaId'] ?? null,
            'depot_code' => $data['depotCode'],
            'depot_name' => $data['depotName'],
            'depot_manager' => $data['depotManager'],
            'depot_manager_contact' => $data['depotManagerContact'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new DepotView($item->fresh(['region', 'area', 'user'])))->resolve(),
            'message' => 'Depot created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDepotRequest $request): JsonResponse
    {
        $data = $request->validated();
        $depot = $this->findByUuid($uuid);

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

        return response()->json([
            'data' => (new DepotView($depot->fresh(['region', 'area', 'user'])))->resolve(),
            'message' => 'Depot updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Depot deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Depot
    {
        return Depot::with(['region', 'area', 'user'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toSelectOption(Depot $depot): array
    {
        return [
            'id' => $depot->id,
            'uuid' => $depot->uuid,
            'depotCode' => $depot->depot_code,
            'depotName' => $depot->depot_name,
        ];
    }
}

