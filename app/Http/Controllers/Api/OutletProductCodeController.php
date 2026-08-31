<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutletProductCodeRequest;
use App\Http\Requests\UpdateOutletProductCodeRequest;
use App\Repositories\OutletProductCodeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletProductCodeController extends Controller
{
    public function __construct(protected OutletProductCodeRepository $outletProductCodes) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->outletProductCodes->list(
            $request->only(['search']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->outletProductCodes->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Outlet product codes retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->outletProductCodes->all(
            $request->only(['search']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->outletProductCodes->toSelectOption($item))->values(),
            'message' => 'Outlet product codes retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->outletProductCodes->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->outletProductCodes->toResource($item),
            'message' => 'Outlet product code retrieved successfully.',
        ]);
    }

    public function store(StoreOutletProductCodeRequest $request): JsonResponse
    {
        $item = $this->outletProductCodes->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->outletProductCodes->toResource($item),
            'message' => 'Outlet product code created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateOutletProductCodeRequest $request): JsonResponse
    {
        $item = $this->outletProductCodes->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->outletProductCodes->toResource($item),
            'message' => 'Outlet product code updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->outletProductCodes->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Outlet product code deleted successfully.']);
    }
}
