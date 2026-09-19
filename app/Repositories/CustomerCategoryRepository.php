<?php

namespace App\Repositories;

use App\Http\Requests\StoreCustomerCategoryRequest;
use App\Http\Requests\UpdateCustomerCategoryRequest;
use App\Models\CustomerCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCategoryRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = CustomerCategory::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (CustomerCategory $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'customerCategories'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = CustomerCategory::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (CustomerCategory $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'customerCategories'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer category retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = CustomerCategory::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = CustomerCategory::create([
            'customer_category_code' => $data['customerCategoryCode'] ?? $data['code'] ?? '',
            'parent_id' => $parentId,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'customer_category_name' => $data['categoryName'] ?? $data['customerCategoryName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer category created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $category = $this->findByUuid($uuid);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $category->parent_id;
        $nodeLevel = $category->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = CustomerCategory::where('id', $parentId)->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $category->fill([
            'customer_category_code' => $data['customerCategoryCode'] ?? $data['code'] ?? $category->customer_category_code,
            'parent_id' => $parentId,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'customer_category_name' => $data['categoryName'] ?? $data['customerCategoryName'] ?? $category->customer_category_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $category->status,
        ]);
        $category->save();

        return response()->json([
            'data' => $this->toResource($category->fresh()),
            'message' => 'Customer category updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Customer category deleted successfully.']);
    }

    protected function findByUuid(string $uuid): CustomerCategory
    {
        return CustomerCategory::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(CustomerCategory $category): array
    {
        return [
            'id' => $category->id,
            'uuid' => $category->uuid,
            'categoryName' => $category->customer_category_name,
            'customerCategoryCode' => $category->customer_category_code,
            'customerCategoryName' => $category->customer_category_name,
            'parentId' => $category->parent_id,
            'nodeLevel' => $category->node_level,
            'status' => (bool) $category->status,
        ];
    }

    protected function toSelectOption(CustomerCategory $category): array
    {
        return [
            'id' => $category->id,
            'uuid' => $category->uuid,
            'categoryName' => $category->customer_category_name,
            'name' => $category->customer_category_name,
        ];
    }
}
