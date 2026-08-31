<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Repositories\RouteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function __construct(protected RouteRepository $routes) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->routes->list(
            $request->only(['search', 'area_id', 'depot_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->routes->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Routes retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->routes->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->routes->toSelectOption($item))->values(),
            'message' => 'Routes retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->routes->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->routes->toResource($item),
            'message' => 'Route retrieved successfully.',
        ]);
    }

    public function store(StoreRouteRequest $request): JsonResponse
    {
        $item = $this->routes->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->routes->toResource($item),
            'message' => 'Route created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateRouteRequest $request): JsonResponse
    {
        $item = $this->routes->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->routes->toResource($item),
            'message' => 'Route updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->routes->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Route deleted successfully.']);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->routes->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Route deleted successfully.']);
    }
}
