<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVanRequest;
use App\Http\Requests\UpdateVanRequest;
use App\Repositories\VanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanController extends Controller
{
    public function __construct(protected VanRepository $vans) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->vans->list(
            $request->only(['search', 'area_id', 'van_type_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->vans->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Vans retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->vans->all(
            $request->only(['search', 'area_id', 'van_type_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->vans->toSelectOption($item))->values(),
            'message' => 'Vans retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->vans->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->vans->toResource($item),
            'message' => 'Van retrieved successfully.',
        ]);
    }

    public function store(StoreVanRequest $request): JsonResponse
    {
        $item = $this->vans->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->vans->toResource($item),
            'message' => 'Van created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateVanRequest $request): JsonResponse
    {
        $item = $this->vans->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->vans->toResource($item),
            'message' => 'Van updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->vans->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Van deleted successfully.']);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->vans->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Van deleted successfully.']);
    }
}
