<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkOrderActionRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderRepository $orders) {}

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
        $items = $this->orders->all(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($order) => $this->orders->toSelectOption($order))->values(),
            'message' => 'Orders retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $order = $this->orders->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->orders->toResource($order),
            'message' => 'Order retrieved successfully.',
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orders->create(
            $request->validated(),
            $request->user()->organisation_id,
            $request->user()->id,
        );

        return response()->json([
            'data' => $this->orders->toResource($order),
            'message' => 'Order created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateOrderRequest $request): JsonResponse
    {
        $order = $this->orders->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->orders->toResource($order),
            'message' => 'Order updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->orders->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function bulkAction(BulkOrderActionRequest $request): JsonResponse
    {
        $this->orders->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->orders->list(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($order) => $this->orders->toResource($order))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Orders retrieved successfully.',
        ]);
    }
}
