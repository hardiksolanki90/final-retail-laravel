<?php

namespace App\Repositories;

use App\Http\Requests\StoreItemCategoryRequest;
use App\Http\Requests\UpdateItemCategoryRequest;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemCategoryRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = ItemCategory::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (ItemCategory $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'itemCategories'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = ItemCategory::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (ItemCategory $item) => $this->toResource($item))->values(),
            'message' => 'Item categories retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item category retrieved successfully.',
        ]);
    }

    public function store(StoreItemCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = ItemCategory::create([
            'category_name' => $data['categoryName'] ?? '',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Item category created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $itemCategory = $this->findByUuid($uuid);

        $itemCategory->fill([
            'category_name' => $data['categoryName'] ?? $itemCategory->category_name,
            'description' => $data['description'] ?? $itemCategory->description,
            'status' => array_key_exists('status', $data) ? $data['status'] : $itemCategory->status,
        ]);
        $itemCategory->save();

        return response()->json([
            'data' => $this->toResource($itemCategory->fresh()),
            'message' => 'Item category updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Item category deleted successfully.']);
    }

    protected function findByUuid(string $uuid): ItemCategory
    {
        return ItemCategory::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(ItemCategory $itemCategory): array
    {
        return [
            'id' => $itemCategory->id,
            'uuid' => $itemCategory->uuid,
            'categoryName' => $itemCategory->category_name,
            'description' => $itemCategory->description,
            'status' => (bool) $itemCategory->status,
        ];
    }
}
