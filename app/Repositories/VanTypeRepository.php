<?php

namespace App\Repositories;

use App\Http\Requests\StoreVanTypeRequest;
use App\Models\VanType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanTypeRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = VanType::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (VanType $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'vanTypes'), 200);
    }

    public function store(StoreVanTypeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = VanType::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = VanType::create([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'node_level' => $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toSelectOption($item),
            'message' => 'Van type created successfully.',
        ], 201);
    }

    protected function toSelectOption(VanType $vanType): array
    {
        return [
            'id' => $vanType->id,
            'uuid' => $vanType->uuid,
            'name' => $vanType->name,
        ];
    }
}
