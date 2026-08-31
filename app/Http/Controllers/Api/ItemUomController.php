<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemUomRequest;
use App\Http\Requests\UpdateItemUomRequest;
use App\Repositories\ItemUomRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemUomController extends Controller
{
    public function __construct(protected ItemUomRepository $itemUoms) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->itemUoms->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->itemUoms->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Item UOMs retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->itemUoms->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->itemUoms->toSelectOption($item))->values(),
            'message' => 'Item UOMs retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->itemUoms->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemUoms->toResource($item),
            'message' => 'Item UOM retrieved successfully.',
        ]);
    }

    public function store(StoreItemUomRequest $request): JsonResponse
    {
        $item = $this->itemUoms->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemUoms->toResource($item),
            'message' => 'Item UOM created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateItemUomRequest $request): JsonResponse
    {
        $item = $this->itemUoms->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->itemUoms->toResource($item),
            'message' => 'Item UOM updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->itemUoms->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Item UOM deleted successfully.']);
    }
}
