<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDebitNoteActionRequest;
use App\Http\Requests\StoreDebitNoteRequest;
use App\Http\Requests\UpdateDebitNoteRequest;
use App\Repositories\DebitNoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function __construct(protected DebitNoteRepository $debitNotes) {}

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
        $items = $this->debitNotes->all(
            $request->only(['search', 'customer_id', 'salesman_id', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($debitNote) => $this->debitNotes->toSelectOption($debitNote))->values(),
            'message' => 'Debit notes retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $debitNote = $this->debitNotes->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->debitNotes->toResource($debitNote),
            'message' => 'Debit note retrieved successfully.',
        ]);
    }

    public function store(StoreDebitNoteRequest $request): JsonResponse
    {
        $debitNote = $this->debitNotes->create(
            $request->validated(),
            $request->user()->organisation_id,
            $request->user()->id,
        );

        return response()->json([
            'data' => $this->debitNotes->toResource($debitNote),
            'message' => 'Debit note created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDebitNoteRequest $request): JsonResponse
    {
        $debitNote = $this->debitNotes->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->debitNotes->toResource($debitNote),
            'message' => 'Debit note updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->debitNotes->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Debit note deleted successfully.']);
    }

    public function bulkAction(BulkDebitNoteActionRequest $request): JsonResponse
    {
        $this->debitNotes->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->debitNotes->list(
            $request->only(['search', 'customer_id', 'salesman_id', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($debitNote) => $this->debitNotes->toResource($debitNote))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Debit notes retrieved successfully.',
        ]);
    }
}
