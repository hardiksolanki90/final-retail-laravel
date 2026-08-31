<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkItemActionRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Repositories\ItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(protected ItemRepository $items) {}

    public function list(Request $request): JsonResponse
    {
        return $this->paginatedResponse($request);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->paginatedResponse($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->items->all(
            $request->only(['search', 'item_category_id', 'brand_id', 'status', 'is_new_launch']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->items->toSelectOption($item))->values(),
            'message' => 'Items retrieved successfully.',
        ]);
    }

    public function withStock(Request $request): JsonResponse
    {
        $data = $this->items->withStock(
            $request->only(['search', 'item_category_id', 'brand_id', 'status', 'warehouse_id']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $data->values(),
            'message' => 'Items with stock retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->items->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->items->toResource($item),
            'message' => 'Item retrieved successfully.',
        ]);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = $this->items->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->items->toResource($item),
            'message' => 'Item created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemRequest $request): JsonResponse
    {
        $item = $this->items->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->items->toResource($item),
            'message' => 'Item updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->items->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Item deleted successfully.']);
    }

    public function bulkAction(BulkItemActionRequest $request): JsonResponse
    {
        $this->items->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->items->list(
            $request->only(['search', 'item_category_id', 'brand_id', 'status', 'is_new_launch']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->items->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Items retrieved successfully.',
        ]);
    }
}
