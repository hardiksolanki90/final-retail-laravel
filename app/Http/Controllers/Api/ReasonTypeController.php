<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReasonTypeRequest;
use App\Http\Requests\UpdateReasonTypeRequest;
use App\Repositories\ReasonTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReasonTypeController extends Controller
{
    public function __construct(protected ReasonTypeRepository $reasonTypes) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->reasonTypes->list(
            $request->only(['search', 'status', 'type']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->reasonTypes->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Reason types retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->reasonTypes->all(
            $request->only(['search', 'status', 'type']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->reasonTypes->toSelectOption($item))->values(),
            'message' => 'Reason types retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->reasonTypes->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->reasonTypes->toResource($item),
            'message' => 'Reason type retrieved successfully.',
        ]);
    }

    public function store(StoreReasonTypeRequest $request): JsonResponse
    {
        $item = $this->reasonTypes->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->reasonTypes->toResource($item),
            'message' => 'Reason type created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateReasonTypeRequest $request): JsonResponse
    {
        $item = $this->reasonTypes->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->reasonTypes->toResource($item),
            'message' => 'Reason type updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->reasonTypes->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Reason type deleted successfully.']);
    }
}
