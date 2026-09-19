<?php

namespace App\Repositories;

use App\Http\Requests\StoreVanCategoryRequest;
use App\Models\VanCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanCategoryRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = VanCategory::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (VanCategory $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'vanCategories'), 200);
    }

    public function store(StoreVanCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = VanCategory::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = VanCategory::create([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'node_level' => $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toSelectOption($item),
            'message' => 'Van category created successfully.',
        ], 201);
    }

    protected function toSelectOption(VanCategory $vanCategory): array
    {
        return [
            'id' => $vanCategory->id,
            'uuid' => $vanCategory->uuid,
            'name' => $vanCategory->name,
        ];
    }
}
