<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkSalesmanActionRequest;
use App\Http\Requests\StoreSalesmanRequest;
use App\Http\Requests\UpdateSalesmanRequest;
use App\Repositories\SalesmanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanController extends Controller
{
    public function __construct(protected SalesmanRepository $salesmen) {}

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
        $items = $this->salesmen->all(
            $request->only(['route_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($salesman) => $this->salesmen->toSelectOption($salesman))->values(),
            'message' => 'Salesmen retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $salesman = $this->salesmen->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->salesmen->toResource($salesman),
            'message' => 'Salesman retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanRequest $request): JsonResponse
    {
        $salesman = $this->salesmen->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->salesmen->toResource($salesman),
            'message' => 'Salesman created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanRequest $request): JsonResponse
    {
        $salesman = $this->salesmen->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->salesmen->toResource($salesman),
            'message' => 'Salesman updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->salesmen->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Salesman deleted successfully.']);
    }

    public function bulkAction(BulkSalesmanActionRequest $request): JsonResponse
    {
        $this->salesmen->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    public function sales(string $uuid, Request $request): JsonResponse
    {
        $data = $this->salesmen->sales($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $data,
            'message' => 'Salesman sales retrieved successfully.',
        ]);
    }

    public function loginHistory(int $userId, Request $request): JsonResponse
    {
        $data = $this->salesmen->loginHistory($userId, (int) $request->query('limit', 20));

        return response()->json([
            'data' => $data,
            'message' => 'Salesman login history retrieved successfully.',
        ]);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->salesmen->list(
            $request->only(['search', 'route_id', 'salesman_type_id', 'salesman_role_id', 'supervisor_id', 'status', 'is_blocked']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($salesman) => $this->salesmen->toResource($salesman))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Salesmen retrieved successfully.',
        ]);
    }
}
