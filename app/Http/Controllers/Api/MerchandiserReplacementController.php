<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchandiserReplacementRequest;
use App\Http\Requests\UpdateMerchandiserReplacementRequest;
use App\Repositories\MerchandiserReplacementRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchandiserReplacementController extends Controller
{
    public function __construct(protected MerchandiserReplacementRepository $merchandiserReplacements) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->merchandiserReplacements->list(
            $request->only(['old_salesman_id', 'new_salesman_id']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->merchandiserReplacements->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Merchandiser replacements retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->merchandiserReplacements->all(
            $request->only(['old_salesman_id', 'new_salesman_id']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->merchandiserReplacements->toSelectOption($item))->values(),
            'message' => 'Merchandiser replacements retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->merchandiserReplacements->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->merchandiserReplacements->toResource($item),
            'message' => 'Merchandiser replacement retrieved successfully.',
        ]);
    }

    public function store(StoreMerchandiserReplacementRequest $request): JsonResponse
    {
        $item = $this->merchandiserReplacements->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->merchandiserReplacements->toResource($item),
            'message' => 'Merchandiser replacement created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateMerchandiserReplacementRequest $request): JsonResponse
    {
        $item = $this->merchandiserReplacements->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->merchandiserReplacements->toResource($item),
            'message' => 'Merchandiser replacement updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->merchandiserReplacements->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Merchandiser replacement deleted successfully.']);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->merchandiserReplacements->delete($uuid, $request->user()->organisation_id);

        return response()->json(['message' => 'Merchandiser replacement deleted successfully.']);
    }
}
