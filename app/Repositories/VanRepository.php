<?php

namespace App\Repositories;

use App\Http\Requests\StoreVanRequest;
use App\Http\Requests\UpdateVanRequest;
use App\Models\Van;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Van::filter($this->filters($request))
            ->with(['area', 'vanType', 'vanCategory'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Van $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'vans'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = Van::filter($this->filters($request))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (Van $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'vans'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Van retrieved successfully.',
        ]);
    }

    public function store(StoreVanRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Van::create([
            'van_code' => $data['vanCode'] ?? '',
            'plate_number' => $data['plateNumber'] ?? '',
            'description' => $data['description'] ?? '',
            'capacity' => $data['capacity'] ?? null,
            'area_id' => $data['areaId'] ?? null,
            'van_type_id' => $data['vanTypeId'] ?? null,
            'van_category_id' => $data['vanCategoryId'] ?? null,
            'van_status' => $data['status'] ?? true,
            'reading' => $data['reading'] ?? 0,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Van created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateVanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $van = $this->findByUuid($uuid);

        $van->fill([
            'van_code' => $data['vanCode'] ?? $van->van_code,
            'plate_number' => $data['plateNumber'] ?? $van->plate_number,
            'description' => $data['description'] ?? $van->description,
            'capacity' => array_key_exists('capacity', $data) ? $data['capacity'] : $van->capacity,
            'area_id' => array_key_exists('areaId', $data) ? $data['areaId'] : $van->area_id,
            'van_type_id' => $data['vanTypeId'] ?? $van->van_type_id,
            'van_category_id' => array_key_exists('vanCategoryId', $data) ? $data['vanCategoryId'] : $van->van_category_id,
            'van_status' => array_key_exists('status', $data) ? $data['status'] : $van->van_status,
            'reading' => array_key_exists('reading', $data) ? $data['reading'] : $van->reading,
        ]);
        $van->save();

        return response()->json([
            'data' => $this->toResource($van->fresh(['area', 'vanType', 'vanCategory'])),
            'message' => 'Van updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Van deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Van
    {
        return Van::with(['area', 'vanType', 'vanCategory'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Van's boolean column is `van_status`, not `status`, so the public
     * `status` query param is remapped to the model's filterable key.
     * Filterable::scopeFilter only auto-casts a literal `status` key to
     * boolean, so the value is cast here before it reaches the trait.
     */
    protected function filters(Request $request): array
    {
        $filters = $request->only(['search', 'area_id', 'van_type_id', 'status']);

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $filters['van_status'] = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN);
        }
        unset($filters['status']);

        return $filters;
    }

    protected function toResource(Van $van): array
    {
        return [
            'id' => $van->id,
            'uuid' => $van->uuid,
            'vanCode' => $van->van_code,
            'plateNumber' => $van->plate_number,
            'description' => $van->description,
            'capacity' => $van->capacity,
            'areaId' => $van->area_id,
            'vanTypeId' => $van->van_type_id,
            'vanCategoryId' => $van->van_category_id,
            'status' => (bool) $van->van_status,
            'reading' => $van->reading,
            'area' => $van->relationLoaded('area') && $van->area
                ? ['id' => $van->area->id, 'uuid' => $van->area->uuid, 'name' => $van->area->area_name]
                : null,
            'vanType' => $van->relationLoaded('vanType') && $van->vanType
                ? ['id' => $van->vanType->id, 'uuid' => $van->vanType->uuid, 'name' => $van->vanType->name]
                : null,
            'vanCategory' => $van->relationLoaded('vanCategory') && $van->vanCategory
                ? ['id' => $van->vanCategory->id, 'uuid' => $van->vanCategory->uuid, 'name' => $van->vanCategory->name]
                : null,
        ];
    }

    protected function toSelectOption(Van $van): array
    {
        return [
            'id' => $van->id,
            'uuid' => $van->uuid,
            'vanCode' => $van->van_code,
            'plateNumber' => $van->plate_number,
        ];
    }
}
