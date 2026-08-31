<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkSalesmanLoadActionRequest;
use App\Http\Requests\StoreSalesmanLoadRequest;
use App\Http\Requests\UpdateSalesmanLoadRequest;
use App\Repositories\SalesmanLoadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanLoadController extends Controller
{
    public function __construct(protected SalesmanLoadRepository $salesmanLoads) {}

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
        $items = $this->salesmanLoads->all(
            $request->only(['search', 'salesman_id', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($load) => $this->salesmanLoads->toSelectOption($load))->values(),
            'message' => 'Salesman loads retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $load = $this->salesmanLoads->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->salesmanLoads->toResource($load),
            'message' => 'Salesman load retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanLoadRequest $request): JsonResponse
    {
        $load = $this->salesmanLoads->create(
            $request->validated(),
            $request->user()->organisation_id,
            $request->user()->id,
        );

        return response()->json([
            'data' => $this->salesmanLoads->toResource($load),
            'message' => 'Salesman load created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanLoadRequest $request): JsonResponse
    {
        $load = $this->salesmanLoads->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->salesmanLoads->toResource($load),
            'message' => 'Salesman load updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->salesmanLoads->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Salesman load deleted successfully.']);
    }

    public function bulkAction(BulkSalesmanLoadActionRequest $request): JsonResponse
    {
        $this->salesmanLoads->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->salesmanLoads->list(
            $request->only(['search', 'salesman_id', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($load) => $this->salesmanLoads->toResource($load))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Salesman loads retrieved successfully.',
        ]);
    }
}
