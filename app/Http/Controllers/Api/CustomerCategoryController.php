<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerCategoryRequest;
use App\Http\Requests\UpdateCustomerCategoryRequest;
use App\Repositories\CustomerCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCategoryController extends Controller
{
    public function __construct(protected CustomerCategoryRepository $customerCategories) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->customerCategories->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->customerCategories->toSelectOption($item))->values(),
            'message' => 'Customer categories retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->customerCategories->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerCategories->toResource($item),
            'message' => 'Customer category retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerCategoryRequest $request): JsonResponse
    {
        $item = $this->customerCategories->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerCategories->toResource($item),
            'message' => 'Customer category created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerCategoryRequest $request): JsonResponse
    {
        $item = $this->customerCategories->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->customerCategories->toResource($item),
            'message' => 'Customer category updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->customerCategories->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Customer category deleted successfully.']);
    }
}
