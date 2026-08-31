<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Repositories\WarehouseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(protected WarehouseRepository $warehouses) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->warehouses->list(
            $request->only(['search', 'depot_id', 'route_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->warehouses->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Warehouses retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->warehouses->all(
            $request->only(['search', 'depot_id', 'route_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->warehouses->toSelectOption($item))->values(),
            'message' => 'Warehouses retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->warehouses->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->warehouses->toResource($item),
            'message' => 'Warehouse retrieved successfully.',
        ]);
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $item = $this->warehouses->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->warehouses->toResource($item),
            'message' => 'Warehouse created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateWarehouseRequest $request): JsonResponse
    {
        $item = $this->warehouses->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->warehouses->toResource($item),
            'message' => 'Warehouse updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->warehouses->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Warehouse deleted successfully.']);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->warehouses->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Warehouse deleted successfully.']);
    }
}
