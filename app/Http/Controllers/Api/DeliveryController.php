<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeliveryActionRequest;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Repositories\DeliveryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(protected DeliveryRepository $deliveries) {}

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
        $items = $this->deliveries->all(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($delivery) => $this->deliveries->toSelectOption($delivery))->values(),
            'message' => 'Deliveries retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $delivery = $this->deliveries->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->deliveries->toResource($delivery),
            'message' => 'Delivery retrieved successfully.',
        ]);
    }

    public function store(StoreDeliveryRequest $request): JsonResponse
    {
        $delivery = $this->deliveries->create(
            $request->validated(),
            $request->user()->organisation_id,
            $request->user()->id,
        );

        return response()->json([
            'data' => $this->deliveries->toResource($delivery),
            'message' => 'Delivery created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDeliveryRequest $request): JsonResponse
    {
        $delivery = $this->deliveries->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->deliveries->toResource($delivery),
            'message' => 'Delivery updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->deliveries->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Delivery deleted successfully.']);
    }

    public function bulkAction(BulkDeliveryActionRequest $request): JsonResponse
    {
        $this->deliveries->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->deliveries->list(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($delivery) => $this->deliveries->toResource($delivery))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Deliveries retrieved successfully.',
        ]);
    }
}
