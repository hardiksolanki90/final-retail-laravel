<?php

namespace App\Repositories;

use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Http\Resources\RegionList;
use App\Http\Resources\RegionView;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Region::filter($this->filters($request))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Region $item) => (new RegionList($item))->resolve());

        return response()->json(paginated($paginated, 'regions'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Region::filter($this->filters($request))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Region $item) => $this->toSelectOption($item))->values(),
            'message' => 'Regions retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new RegionView($item))->resolve(),
            'message' => 'Region retrieved successfully.',
        ]);
    }

    public function store(StoreRegionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $countryId = $data['countryId'] ?? \App\Models\Country::value('id') ?? 1;

        $item = Region::create([
            'country_id' => $countryId,
            'region_code' => $data['regionCode'] ?? $data['code'] ?? '',
            'region_name' => $data['regionName'] ?? $data['name'] ?? '',
            'region_status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new RegionView($item))->resolve(),
            'message' => 'Region created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateRegionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $region = $this->findByUuid($uuid);

        $region->fill([
            'country_id' => $data['countryId'] ?? $region->country_id,
            'region_code' => $data['regionCode'] ?? $region->region_code,
            'region_name' => $data['regionName'] ?? $region->region_name,
            'region_status' => array_key_exists('status', $data) ? $data['status'] : $region->region_status,
        ]);
        $region->save();

        return response()->json([
            'data' => (new RegionView($region->fresh()))->resolve(),
            'message' => 'Region updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Region deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Region
    {
        return Region::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Region's boolean column is `region_status`, not `status`, so the
     * public `status` query param is remapped to the model's filterable key.
     * Filterable::scopeFilter only auto-casts a literal `status` key to
     * boolean, so the value is cast here before it reaches the trait.
     */
    protected function filters(Request $request): array
    {
        $filters = $request->only(['search', 'country_id', 'status']);

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $filters['region_status'] = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN);
        }
        unset($filters['status']);

        return $filters;
    }

    protected function toSelectOption(Region $region): array
    {
        return [
            'id' => $region->id,
            'uuid' => $region->uuid,
            'regionCode' => $region->region_code,
            'regionName' => $region->region_name,
        ];
    }
}

