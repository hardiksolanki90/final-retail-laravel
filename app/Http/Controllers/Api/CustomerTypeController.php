<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerTypeRequest;
use App\Http\Requests\UpdateCustomerTypeRequest;
use App\Repositories\CustomerTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    public function __construct(protected CustomerTypeRepository $customerTypes) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->customerTypes->all($request->only(['search', 'status']));

        return response()->json([
            'data' => $items->map(fn ($item) => $this->customerTypes->toSelectOption($item))->values(),
            'message' => 'Customer types retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->customerTypes->findByUuid($uuid);

        return response()->json([
            'data' => $this->customerTypes->toResource($item),
            'message' => 'Customer type retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerTypeRequest $request): JsonResponse
    {
        $item = $this->customerTypes->create($request->validated());

        return response()->json([
            'data' => $this->customerTypes->toResource($item),
            'message' => 'Customer type created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerTypeRequest $request): JsonResponse
    {
        $item = $this->customerTypes->update($uuid, $request->validated());

        return response()->json([
            'data' => $this->customerTypes->toResource($item),
            'message' => 'Customer type updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->customerTypes->delete((string) $request->input('id'));

        return response()->json(['message' => 'Customer type deleted successfully.']);
    }
}
