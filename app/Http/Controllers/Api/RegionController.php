<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Repositories\RegionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function __construct(protected RegionRepository $regions) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->regions->list(
            $request->only(['search', 'country_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->regions->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Regions retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->regions->all(
            $request->only(['search', 'country_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->regions->toSelectOption($item))->values(),
            'message' => 'Regions retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->regions->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->regions->toResource($item),
            'message' => 'Region retrieved successfully.',
        ]);
    }

    public function store(StoreRegionRequest $request): JsonResponse
    {
        $item = $this->regions->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->regions->toResource($item),
            'message' => 'Region created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateRegionRequest $request): JsonResponse
    {
        $item = $this->regions->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->regions->toResource($item),
            'message' => 'Region updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->regions->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Region deleted successfully.']);
    }
}
