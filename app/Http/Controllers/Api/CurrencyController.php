<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(protected CurrencyRepository $currencies) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->currencies->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->currencies->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Currencies retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->currencies->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->currencies->toSelectOption($item))->values(),
            'message' => 'Currencies retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->currencies->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->currencies->toResource($item),
            'message' => 'Currency retrieved successfully.',
        ]);
    }

    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $item = $this->currencies->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->currencies->toResource($item),
            'message' => 'Currency created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCurrencyRequest $request): JsonResponse
    {
        $item = $this->currencies->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->currencies->toResource($item),
            'message' => 'Currency updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->currencies->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Currency deleted successfully.']);
    }
}
