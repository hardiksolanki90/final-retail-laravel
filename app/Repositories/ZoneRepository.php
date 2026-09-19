<?php

namespace App\Repositories;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Http\Resources\ZoneList;
use App\Http\Resources\ZoneView;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Zone::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Zone $item) => (new ZoneList($item))->resolve());

        return response()->json(paginated($paginated, 'zones'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Zone::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Zone $item) => $this->toSelectOption($item))->values(),
            'message' => 'Zones retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new ZoneView($item))->resolve(),
            'message' => 'Zone retrieved successfully.',
        ]);
    }

    public function store(StoreZoneRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Zone::create([
            'zone_code' => $data['zoneCode'],
            'name' => $data['name'],
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new ZoneView($item))->resolve(),
            'message' => 'Zone created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateZoneRequest $request): JsonResponse
    {
        $data = $request->validated();
        $zone = $this->findByUuid($uuid);

        $zone->fill([
            'zone_code' => $data['zoneCode'] ?? $zone->zone_code,
            'name' => $data['name'] ?? $zone->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $zone->status,
        ]);
        $zone->save();

        return response()->json([
            'data' => (new ZoneView($zone->fresh()))->resolve(),
            'message' => 'Zone updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Zone deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Zone
    {
        return Zone::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Zone $zone): array
    {
        return [
            'id' => $zone->id,
            'uuid' => $zone->uuid,
            'name' => $zone->name,
        ];
    }
}

