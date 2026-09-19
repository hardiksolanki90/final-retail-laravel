<?php

namespace App\Repositories;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Brand::filter($request->only(['search', 'status', 'parent_id']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Brand $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'brands'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Brand::filter($request->only(['search', 'status', 'parent_id']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Brand $item) => $this->toResource($item))->values(),
            'message' => 'Brands retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Brand retrieved successfully.',
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $request->validated();
        $parent = ! empty($data['parentId']) ? Brand::find($data['parentId']) : null;

        $item = Brand::create([
            'parent_id' => $parent?->id,
            'brand_name' => $data['brandName'] ?? '',
            'description' => $data['description'] ?? null,
            'logo_url' => $data['logoUrl'] ?? null,
            'node_level' => $parent ? $parent->node_level + 1 : 0,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Brand created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateBrandRequest $request): JsonResponse
    {
        $data = $request->validated();
        $brand = $this->findByUuid($uuid);
        $parent = array_key_exists('parentId', $data) && $data['parentId']
            ? Brand::find($data['parentId'])
            : null;

        $brand->fill([
            'parent_id' => array_key_exists('parentId', $data) ? $parent?->id : $brand->parent_id,
            'brand_name' => $data['brandName'] ?? $brand->brand_name,
            'description' => $data['description'] ?? $brand->description,
            'logo_url' => $data['logoUrl'] ?? $brand->logo_url,
            'node_level' => array_key_exists('parentId', $data) ? ($parent ? $parent->node_level + 1 : 0) : $brand->node_level,
            'status' => array_key_exists('status', $data) ? $data['status'] : $brand->status,
        ]);
        $brand->save();

        return response()->json([
            'data' => $this->toResource($brand->fresh()),
            'message' => 'Brand updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Brand deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Brand
    {
        return Brand::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'uuid' => $brand->uuid,
            'parentId' => $brand->parent_id,
            'brandName' => $brand->brand_name,
            'description' => $brand->description,
            'logoUrl' => $brand->logo_url,
            'nodeLevel' => $brand->node_level,
            'status' => (bool) $brand->status,
        ];
    }
}
