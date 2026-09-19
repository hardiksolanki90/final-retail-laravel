<?php

namespace App\Repositories;

use App\Http\Requests\StoreItemUomRequest;
use App\Http\Requests\UpdateItemUomRequest;
use App\Models\ItemUom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemUomRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = ItemUom::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (ItemUom $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'itemUoms'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = ItemUom::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (ItemUom $item) => $this->toSelectOption($item))->values(),
            'message' => 'Item UOMs retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item UOM retrieved successfully.',
        ]);
    }

    public function store(StoreItemUomRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = ItemUom::create([
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item UOM created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemUomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $itemUom = $this->findByUuid($uuid);

        $itemUom->fill([
            'code' => $data['code'] ?? $itemUom->code,
            'name' => $data['name'] ?? $itemUom->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $itemUom->status,
        ]);
        $itemUom->save();

        return response()->json([
            'data' => $this->toResource($itemUom->fresh()),
            'message' => 'Item UOM updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Item UOM deleted successfully.']);
    }

    protected function findByUuid(string $uuid): ItemUom
    {
        return ItemUom::where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(ItemUom $itemUom): array
    {
        return [
            'id' => $itemUom->id,
            'uuid' => $itemUom->uuid,
            'code' => $itemUom->code,
            'name' => $itemUom->name,
            'status' => (bool) $itemUom->status,
        ];
    }

    protected function toSelectOption(ItemUom $itemUom): array
    {
        return [
            'id' => $itemUom->id,
            'uuid' => $itemUom->uuid,
            'code' => $itemUom->code,
            'name' => $itemUom->name,
        ];
    }
}
