<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Repositories\ZoneRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function __construct(protected ZoneRepository $zones) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->zones->list(
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->zones->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Zones retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->zones->all($request->only(['search', 'status']));

        return response()->json([
            'data' => $items->map(fn ($item) => $this->zones->toSelectOption($item))->values(),
            'message' => 'Zones retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->zones->findByUuid($uuid);

        return response()->json([
            'data' => $this->zones->toResource($item),
            'message' => 'Zone retrieved successfully.',
        ]);
    }

    public function store(StoreZoneRequest $request): JsonResponse
    {
        $item = $this->zones->create($request->validated());

        return response()->json([
            'data' => $this->zones->toResource($item),
            'message' => 'Zone created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateZoneRequest $request): JsonResponse
    {
        $item = $this->zones->update($uuid, $request->validated());

        return response()->json([
            'data' => $this->zones->toResource($item),
            'message' => 'Zone updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->zones->delete((string) $request->input('id'));

        return response()->json(['message' => 'Zone deleted successfully.']);
    }
}
