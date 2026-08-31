<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankInformationRequest;
use App\Http\Requests\UpdateBankInformationRequest;
use App\Repositories\BankInformationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankInformationController extends Controller
{
    public function __construct(protected BankInformationRepository $bankInformation) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->bankInformation->list(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->bankInformation->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Bank information retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->bankInformation->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->bankInformation->toSelectOption($item))->values(),
            'message' => 'Bank information retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->bankInformation->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->bankInformation->toResource($item),
            'message' => 'Bank information retrieved successfully.',
        ]);
    }

    public function store(StoreBankInformationRequest $request): JsonResponse
    {
        $item = $this->bankInformation->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->bankInformation->toResource($item),
            'message' => 'Bank information created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateBankInformationRequest $request): JsonResponse
    {
        $item = $this->bankInformation->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->bankInformation->toResource($item),
            'message' => 'Bank information updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->bankInformation->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Bank information deleted successfully.']);
    }
}
