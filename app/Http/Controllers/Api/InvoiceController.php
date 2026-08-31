<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkInvoiceActionRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceRepository $invoices) {}

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
        $items = $this->invoices->all(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($invoice) => $this->invoices->toSelectOption($invoice))->values(),
            'message' => 'Invoices retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $invoice = $this->invoices->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->invoices->toResource($invoice),
            'message' => 'Invoice retrieved successfully.',
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->create(
            $request->validated(),
            $request->user()->organisation_id,
            $request->user()->id,
        );

        return response()->json([
            'data' => $this->invoices->toResource($invoice),
            'message' => 'Invoice created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->invoices->toResource($invoice),
            'message' => 'Invoice updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->invoices->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function bulkAction(BulkInvoiceActionRequest $request): JsonResponse
    {
        $this->invoices->bulkAction(
            $request->validated('uuids'),
            $request->validated('action'),
            $request->user()->organisation_id,
        );

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function paginatedResponse(Request $request): JsonResponse
    {
        $paginated = $this->invoices->list(
            $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($invoice) => $this->invoices->toResource($invoice))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'Invoices retrieved successfully.',
        ]);
    }
}
