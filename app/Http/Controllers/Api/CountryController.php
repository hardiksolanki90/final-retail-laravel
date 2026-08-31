<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Repositories\CountryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(protected CountryRepository $countries) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->countries->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->countries->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Countries retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->countries->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->countries->toSelectOption($item))->values(),
            'message' => 'Countries retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->countries->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->countries->toResource($item),
            'message' => 'Country retrieved successfully.',
        ]);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $item = $this->countries->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->countries->toResource($item),
            'message' => 'Country created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCountryRequest $request): JsonResponse
    {
        $item = $this->countries->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->countries->toResource($item),
            'message' => 'Country updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->countries->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Country deleted successfully.']);
    }
}
