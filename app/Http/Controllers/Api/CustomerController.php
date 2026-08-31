<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkCustomerActionRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(protected CustomerRepository $customers) {}

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
        $items = $this->customers->all(
            $request->only(['route_id', 'salesman_id', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($customer) => $this->customers->toSelectOption($customer))->values(),
            'message' => 'Customers retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $customer = $this->customers->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customers->toResource($customer),
            'message' => 'Customer retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customers->toResource($customer),
            'message' => 'Customer created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customers->toResource($customer),
            'message' => 'Customer updated successfully.',
        ]);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $this->customers->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    public function bulkAction(BulkCustomerActionRequest $request): JsonResponse
    {
        $this->customers->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    public function sales(string $uuid, Request $request): JsonResponse
    {
        $data = $this->customers->sales($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $data,
            'message' => 'Customer sales retrieved successfully.',
        ]);
    }

    public function bySalesman(int $salesmanId, Request $request): JsonResponse
    {
        $items = $this->customers->bySalesman($salesmanId, $request->user()->organisation_id);

        return response()->json([
            'data' => $items->map(fn ($customer) => $this->customers->toResource($customer))->values(),
            'message' => 'Customers retrieved successfully.',
        ]);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->customers->list(
            $request->only(['search', 'route_id', 'salesman_id', 'customer_type_id', 'customer_category_id', 'channel_id', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($customer) => $this->customers->toResource($customer))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Customers retrieved successfully.',
        ]);
    }
}
