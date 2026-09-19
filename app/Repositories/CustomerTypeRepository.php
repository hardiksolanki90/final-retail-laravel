<?php

namespace App\Repositories;

use App\Http\Requests\StoreCustomerTypeRequest;
use App\Http\Requests\UpdateCustomerTypeRequest;
use App\Models\CustomerType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerTypeRepository
{
    public function all(Request $request): JsonResponse
    {
        $items = CustomerType::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (CustomerType $item) => $this->toSelectOption($item))->values(),
            'message' => 'Customer types retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer type retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerTypeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = CustomerType::create([
            'customer_type_code' => $data['customerTypeCode'] ?? $data['code'] ?? '',
            'customer_type_name' => $data['name'] ?? $data['customerTypeName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer type created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $customerType = $this->findByUuid($uuid);

        $customerType->fill([
            'customer_type_code' => $data['customerTypeCode'] ?? $data['code'] ?? $customerType->customer_type_code,
            'customer_type_name' => $data['name'] ?? $data['customerTypeName'] ?? $customerType->customer_type_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $customerType->status,
        ]);
        $customerType->save();

        return response()->json([
            'data' => $this->toResource($customerType->fresh()),
            'message' => 'Customer type updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Customer type deleted successfully.']);
    }

    protected function findByUuid(string $uuid): CustomerType
    {
        return CustomerType::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(CustomerType $customerType): array
    {
        return [
            'id' => $customerType->id,
            'uuid' => $customerType->uuid,
            'name' => $customerType->customer_type_name,
            'customerTypeCode' => $customerType->customer_type_code,
            'customerTypeName' => $customerType->customer_type_name,
            'status' => (bool) $customerType->status,
        ];
    }

    protected function toSelectOption(CustomerType $customerType): array
    {
        return [
            'id' => $customerType->id,
            'uuid' => $customerType->uuid,
            'name' => $customerType->customer_type_name,
        ];
    }
}
