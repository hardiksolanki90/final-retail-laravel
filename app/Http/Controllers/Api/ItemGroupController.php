<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemGroupRequest;
use App\Http\Requests\UpdateItemGroupRequest;
use App\Repositories\ItemGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemGroupController extends Controller
{
    public function __construct(protected ItemGroupRepository $itemGroups) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->itemGroups->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->itemGroups->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Item groups retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->itemGroups->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->itemGroups->toSelectOption($item))->values(),
            'message' => 'Item groups retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->itemGroups->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemGroups->toResource($item),
            'message' => 'Item group retrieved successfully.',
        ]);
    }

    public function store(StoreItemGroupRequest $request): JsonResponse
    {
        $item = $this->itemGroups->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemGroups->toResource($item),
            'message' => 'Item group created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemGroupRequest $request): JsonResponse
    {
        $item = $this->itemGroups->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemGroups->toResource($item),
            'message' => 'Item group updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->itemGroups->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Item group deleted successfully.']);
    }
}
