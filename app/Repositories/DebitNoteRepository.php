<?php

namespace App\Repositories;

use App\Http\Requests\BulkDebitNoteActionRequest;
use App\Http\Requests\StoreDebitNoteRequest;
use App\Http\Requests\UpdateDebitNoteRequest;
use App\Models\DebitNote;
use App\Models\DebitNoteDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitNoteRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = DebitNote::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (DebitNote $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'debitNotes'), 200);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = DebitNote::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (DebitNote $item) => $this->toSelectOption($item))->values(),
            'message' => 'Debit notes retrieved successfully.',
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $debitNote = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($debitNote),
            'message' => 'Debit note retrieved successfully.',
        ], 200);
    }

    public function store(StoreDebitNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $debitNote = DB::transaction(function () use ($data, $request) {
            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $debitNote = DebitNote::create(array_merge(
                $this->headerAttributes($data, $request->user()->id),
                $totals,
            ));

            foreach ($lines as $line) {
                $debitNote->details()->create($this->detailAttributes($line));
            }

            return $debitNote->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice']);
        });

        return response()->json([
            'data' => $this->toResource($debitNote),
            'message' => 'Debit note created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDebitNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $debitNote = DB::transaction(function () use ($uuid, $data) {
            $debitNote = DebitNote::where('uuid', $uuid)->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $debitNote->fill(array_merge(
                $this->headerAttributes($data, null, $debitNote),
                $totals,
            ))->save();

            $this->syncDetails($debitNote, $lines);

            return $debitNote->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice']);
        });

        return response()->json([
            'data' => $this->toResource($debitNote),
            'message' => 'Debit note updated successfully.',
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $uuid = (string) $request->input('id');

        DB::transaction(function () use ($uuid) {
            $debitNote = DebitNote::where('uuid', $uuid)->firstOrFail();

            $debitNote->details()->delete();
            $debitNote->delete();
        });

        return response()->json(['message' => 'Debit note deleted successfully.'], 200);
    }

    public function bulkAction(BulkDebitNoteActionRequest $request): JsonResponse
    {
        $query = DebitNote::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (DebitNote $debitNote) {
                $debitNote->details()->delete();
                $debitNote->delete();
            }),
        };

        return response()->json(['message' => 'Bulk action completed successfully.'], 200);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvedFilters(Request $request): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $filters = $request->only(['search', 'customer_id', 'salesman_id', 'status', 'date_from', 'date_to']);

        if (! empty($filters['customer_id'])) {
            $filters['customer_id'] = $this->resolveCustomerId($filters['customer_id'], $organisationId);
        }

        if (! empty($filters['salesman_id'])) {
            $filters['salesman_id'] = $this->resolveSalesmanId($filters['salesman_id'], $organisationId);
        }

        return $filters;
    }

    protected function findByUuid(string $uuid): DebitNote
    {
        return DebitNote::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(DebitNote $debitNote): array
    {
        return [
            'id' => $debitNote->id,
            'uuid' => $debitNote->uuid,
            'debitNoteNumber' => $debitNote->debit_note_number,
            'debitNoteDate' => $debitNote->debit_note_date?->toDateString(),
            'customerId' => $debitNote->customer?->uuid,
            'salesmanId' => $debitNote->salesman?->uuid,
            'invoiceId' => $debitNote->invoice?->uuid,
            'paymentTermId' => $debitNote->paymentTerm?->uuid,
            'routeId' => $debitNote->route_id,
            'reason' => $debitNote->reason,
            'notes' => $debitNote->debit_note_comment,
            'debitNoteType' => $debitNote->debit_note_type,
            'approvalStatus' => $debitNote->approval_status,
            'status' => (bool) $debitNote->status,
            'isDebitNote' => (bool) $debitNote->is_debit_note,
            'totalQty' => (float) $debitNote->total_qty,
            'totalGross' => (float) $debitNote->total_gross,
            'totalDiscountAmount' => (float) $debitNote->total_discount_amount,
            'totalNet' => (float) $debitNote->total_net,
            'totalVat' => (float) $debitNote->total_vat,
            'totalExcise' => (float) $debitNote->total_excise,
            'grandTotal' => (float) $debitNote->grand_total,
            'items' => $debitNote->relationLoaded('details')
                ? $debitNote->details->map(fn (DebitNoteDetail $d) => $this->detailResource($d))->values()->all()
                : [],
        ];
    }

    protected function toSelectOption(DebitNote $debitNote): array
    {
        return [
            'value' => $debitNote->uuid,
            'label' => $debitNote->debit_note_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?int $userId = null, ?DebitNote $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $today = Carbon::today()->toDateString();
        $debitNoteNumber = $data['debitNoteNumber'] ?? $existing?->debit_note_number;
        if ($debitNoteNumber === null || $debitNoteNumber === '') {
            $debitNoteNumber = $this->nextDocumentNumber(DebitNote::class, 'debit_note_number', 'DN', $organisationId);
        }

        $attrs = [
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId),
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'invoice_id' => $this->resolveInvoiceId($data['invoiceId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'payment_term_id' => $this->resolvePaymentTermId(
                $data['paymentTermId'] ?? $data['paymentTerms'] ?? null,
                $organisationId,
            ),
            'reason' => $data['reason'] ?? $existing?->reason,
            'debit_note_number' => $debitNoteNumber,
            'debit_note_date' => $data['debitNoteDate'] ?? $existing?->debit_note_date?->toDateString() ?? $today,
            'debit_note_comment' => $data['notes'] ?? $data['debitNoteComment'] ?? $existing?->debit_note_comment,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
        ];

        if ($existing === null) {
            $attrs['source'] = (int) ($data['source'] ?? 3);
            $attrs['approval_status'] = $data['approvalStatus'] ?? 'Created';
            $attrs['debit_note_type'] = $data['debitNoteType'] ?? 'debit_note';
            $attrs['is_debit_note'] = true;
        }

        return $attrs;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mapLines(array $items): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $lines = [];

        foreach ($items as $item) {
            $computed = $this->computePricedLine($item, $organisationId);
            if (! $computed['item_id']) {
                continue;
            }

            $computed['reason'] = isset($item['reason']) ? (string) $item['reason'] : null;
            $computed['item_condition'] = (string) ($item['itemCondition'] ?? $item['item_condition'] ?? '1');
            $computed['batch_number'] = $item['batchNumber'] ?? $item['batch_number'] ?? null;
            $lines[] = $computed;
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    protected function detailAttributes(array $line): array
    {
        return [
            'uuid' => $line['uuid'],
            'item_id' => $line['item_id'],
            'item_uom_id' => $line['item_uom_id'],
            'item_condition' => $line['item_condition'] ?? '1',
            'item_qty' => $line['item_qty'],
            'item_price' => $line['item_price'],
            'item_gross' => $line['item_gross'],
            'item_discount_amount' => $line['item_discount_amount'],
            'item_net' => $line['item_net'],
            'item_vat' => $line['item_vat'],
            'item_excise' => $line['item_excise'],
            'item_grand_total' => $line['item_grand_total'],
            'reason' => $line['reason'] ?? null,
            'batch_number' => $line['batch_number'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(DebitNote $debitNote, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $debitNote->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $debitNote->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $debitNote->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(DebitNoteDetail $detail): array
    {
        return [
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'itemUomId' => $detail->item_uom_id,
            'itemCondition' => $detail->item_condition,
            'quantity' => (float) $detail->item_qty,
            'price' => (float) $detail->item_price,
            'discount' => (float) $detail->item_discount_amount,
            'vat' => (float) $detail->item_vat,
            'excise' => (float) $detail->item_excise,
            'net' => (float) $detail->item_net,
            'total' => (float) $detail->item_grand_total,
            'reason' => $detail->reason,
            'batchNumber' => $detail->batch_number,
        ];
    }
}
