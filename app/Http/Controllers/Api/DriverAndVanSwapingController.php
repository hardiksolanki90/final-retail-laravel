<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverAndVanSwapingRequest;
use App\Http\Requests\UpdateDriverAndVanSwapingRequest;
use App\Repositories\DriverAndVanSwapingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverAndVanSwapingController extends Controller
{
    public function __construct(protected DriverAndVanSwapingRepository $swapings) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->swapings->list(
            $request->only(['old_salesman_id', 'new_salesman_id', 'reason_id']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->swapings->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Driver and van swapings retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->swapings->all(
            $request->only(['old_salesman_id', 'new_salesman_id', 'reason_id']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->swapings->toSelectOption($item))->values(),
            'message' => 'Driver and van swapings retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->swapings->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->swapings->toResource($item),
            'message' => 'Driver and van swaping retrieved successfully.',
        ]);
    }

    public function store(StoreDriverAndVanSwapingRequest $request): JsonResponse
    {
        $item = $this->swapings->create($request->validated(), $request->user()->organisation_id, $request->user()->id);

        return response()->json([
            'data' => $this->swapings->toResource($item),
            'message' => 'Driver and van swaping created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDriverAndVanSwapingRequest $request): JsonResponse
    {
        $item = $this->swapings->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->swapings->toResource($item),
            'message' => 'Driver and van swaping updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->swapings->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Driver and van swaping deleted successfully.']);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->swapings->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Driver and van swaping deleted successfully.']);
    }
}
