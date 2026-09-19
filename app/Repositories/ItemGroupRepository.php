<?php

namespace App\Repositories;

use App\Http\Requests\StoreItemGroupRequest;
use App\Http\Requests\UpdateItemGroupRequest;
use App\Models\ItemGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemGroupRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = ItemGroup::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (ItemGroup $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'itemGroups'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = ItemGroup::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (ItemGroup $item) => $this->toSelectOption($item))->values(),
            'message' => 'Item groups retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item group retrieved successfully.',
        ]);
    }

    public function store(StoreItemGroupRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = ItemGroup::create([
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item group created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemGroupRequest $request): JsonResponse
    {
        $data = $request->validated();
        $itemGroup = $this->findByUuid($uuid);

        $itemGroup->fill([
            'code' => $data['code'] ?? $itemGroup->code,
            'name' => $data['name'] ?? $itemGroup->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $itemGroup->status,
        ]);
        $itemGroup->save();

        return response()->json([
            'data' => $this->toResource($itemGroup->fresh()),
            'message' => 'Item group updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Item group deleted successfully.']);
    }

    protected function findByUuid(string $uuid): ItemGroup
    {
        return ItemGroup::where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(ItemGroup $itemGroup): array
    {
        return [
            'id' => $itemGroup->id,
            'uuid' => $itemGroup->uuid,
            'code' => $itemGroup->code,
            'name' => $itemGroup->name,
            'status' => (bool) $itemGroup->status,
        ];
    }

    protected function toSelectOption(ItemGroup $itemGroup): array
    {
        return [
            'id' => $itemGroup->id,
            'uuid' => $itemGroup->uuid,
            'code' => $itemGroup->code,
            'name' => $itemGroup->name,
        ];
    }
}
