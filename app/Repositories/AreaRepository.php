<?php

namespace App\Repositories;

use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Http\Resources\AreaList;
use App\Http\Resources\AreaView;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Area::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Area $item) => (new AreaList($item))->resolve());

        return response()->json(paginated($paginated, 'areas'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = Area::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 20))
            ->through(fn (Area $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'areas'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new AreaView($item))->resolve(),
            'message' => 'Area retrieved successfully.',
        ]);
    }

    public function store(StoreAreaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = Area::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = Area::create([
            'area_code' => $data['areaCode'] ?? $data['area_code'] ?? $data['code'] ?? null,
            'parent_id' => $parentId,
            'area_name' => $data['areaName'] ?? $data['name'] ?? '',
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new AreaView($item))->resolve(),
            'message' => 'Area created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateAreaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $area = $this->findByUuid($uuid);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $area->parent_id;
        $nodeLevel = $area->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = Area::where('id', $parentId)->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $area->fill([
            'area_code' => $data['areaCode'] ?? $data['area_code'] ?? $data['code'] ?? $area->area_code,
            'parent_id' => $parentId,
            'area_name' => $data['areaName'] ?? $data['name'] ?? $area->area_name,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => array_key_exists('status', $data) ? $data['status'] : $area->status,
        ]);
        $area->save();

        return response()->json([
            'data' => (new AreaView($area->fresh()))->resolve(),
            'message' => 'Area updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Area deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Area
    {
        return Area::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Area $area): array
    {
        return [
            'id' => $area->id,
            'uuid' => $area->uuid,
            'areaCode' => $area->area_code,
            'code' => $area->area_code,
            'areaName' => $area->area_name,
            'name' => $area->area_name,
        ];
    }
}
