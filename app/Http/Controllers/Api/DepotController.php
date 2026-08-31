<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepotRequest;
use App\Http\Requests\UpdateDepotRequest;
use App\Repositories\DepotRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    public function __construct(protected DepotRepository $depots) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->depots->list(
            $request->only(['search', 'region_id', 'area_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->depots->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Depots retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->depots->all(
            $request->only(['search', 'region_id', 'area_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->depots->toResource($item))->values(),
            'message' => 'Depots retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->depots->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->depots->toResource($item),
            'message' => 'Depot retrieved successfully.',
        ]);
    }

    public function store(StoreDepotRequest $request): JsonResponse
    {
        $item = $this->depots->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->depots->toResource($item),
            'message' => 'Depot created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDepotRequest $request): JsonResponse
    {
        $item = $this->depots->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->depots->toResource($item),
            'message' => 'Depot updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->depots->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Depot deleted successfully.']);
    }
}
