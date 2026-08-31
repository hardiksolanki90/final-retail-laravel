<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerGroupRequest;
use App\Http\Requests\UpdateCustomerGroupRequest;
use App\Repositories\CustomerGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function __construct(protected CustomerGroupRepository $customerGroups) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->customerGroups->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->customerGroups->toSelectOption($item))->values(),
            'message' => 'Customer groups retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->customerGroups->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerGroups->toResource($item),
            'message' => 'Customer group retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        $item = $this->customerGroups->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerGroups->toResource($item),
            'message' => 'Customer group created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerGroupRequest $request): JsonResponse
    {
        $item = $this->customerGroups->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerGroups->toResource($item),
            'message' => 'Customer group updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->customerGroups->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Customer group deleted successfully.']);
    }
}
