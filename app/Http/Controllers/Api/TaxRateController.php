<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Repositories\TaxRateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function __construct(protected TaxRateRepository $taxRates) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->taxRates->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->taxRates->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Tax rates retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->taxRates->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->taxRates->toSelectOption($item))->values(),
            'message' => 'Tax rates retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->taxRates->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->taxRates->toResource($item),
            'message' => 'Tax rate retrieved successfully.',
        ]);
    }

    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        $item = $this->taxRates->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->taxRates->toResource($item),
            'message' => 'Tax rate created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateTaxRateRequest $request): JsonResponse
    {
        $item = $this->taxRates->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->taxRates->toResource($item),
            'message' => 'Tax rate updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->taxRates->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Tax rate deleted successfully.']);
    }
}
