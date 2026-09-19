<?php

namespace App\Repositories;

use App\Http\Requests\BulkCreditNoteActionRequest;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Requests\UpdateCreditNoteRequest;
use App\Models\CreditNote;
use App\Models\CreditNoteDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditNoteRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = CreditNote::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (CreditNote $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'creditNotes'), 200);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = CreditNote::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (CreditNote $item) => $this->toSelectOption($item))->values(),
            'message' => 'Credit notes retrieved successfully.',
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $creditNote = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($creditNote),
            'message' => 'Credit note retrieved successfully.',
        ], 200);
    }

    public function store(StoreCreditNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $creditNote = DB::transaction(function () use ($data, $request) {
            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $creditNote = CreditNote::create(array_merge(
                $this->headerAttributes($data, $request->user()->id),
                $totals,
            ));

            foreach ($lines as $line) {
                $creditNote->details()->create($this->detailAttributes($line));
            }

            return $creditNote->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice']);
        });

        return response()->json([
            'data' => $this->toResource($creditNote),
            'message' => 'Credit note created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCreditNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $creditNote = DB::transaction(function () use ($uuid, $data) {
            $creditNote = CreditNote::where('uuid', $uuid)->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $creditNote->fill(array_merge(
                $this->headerAttributes($data, null, $creditNote),
                $totals,
            ))->save();

            $this->syncDetails($creditNote, $lines);

            return $creditNote->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice']);
        });

        return response()->json([
            'data' => $this->toResource($creditNote),
            'message' => 'Credit note updated successfully.',
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $uuid = (string) $request->input('id');

        DB::transaction(function () use ($uuid) {
            $creditNote = CreditNote::where('uuid', $uuid)->firstOrFail();

            $creditNote->details()->delete();
            $creditNote->delete();
        });

        return response()->json(['message' => 'Credit note deleted successfully.'], 200);
    }

    public function bulkAction(BulkCreditNoteActionRequest $request): JsonResponse
    {
        $query = CreditNote::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (CreditNote $creditNote) {
                $creditNote->details()->delete();
                $creditNote->delete();
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

        $filters = $request->only(['search', 'customer_id', 'status', 'date_from', 'date_to']);

        if (! empty($filters['customer_id'])) {
            $filters['customer_id'] = $this->resolveCustomerId($filters['customer_id'], $organisationId);
        }

        return $filters;
    }

    protected function findByUuid(string $uuid): CreditNote
    {
        return CreditNote::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'invoice'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(CreditNote $creditNote): array
    {
        return [
            'id' => $creditNote->id,
            'uuid' => $creditNote->uuid,
            'creditNoteNumber' => $creditNote->credit_note_number,
            'creditNoteDate' => $creditNote->credit_note_date?->toDateString(),
            'customerId' => $creditNote->customer?->uuid,
            'salesmanId' => $creditNote->salesman?->uuid,
            'invoiceId' => $creditNote->invoice?->uuid,
            'paymentTermId' => $creditNote->paymentTerm?->uuid,
            'routeId' => $creditNote->route_id,
            'reason' => $creditNote->reason,
            'notes' => $creditNote->credit_note_comment,
            'approvalStatus' => $creditNote->approval_status,
            'status' => (bool) $creditNote->status,
            'totalQty' => (float) $creditNote->total_qty,
            'grossTotal' => (float) $creditNote->total_gross,
            'discount' => (float) $creditNote->total_discount_amount,
            'netTotal' => (float) $creditNote->total_net,
            'vat' => (float) $creditNote->total_vat,
            'excise' => (float) $creditNote->total_excise,
            'finalTotal' => (float) $creditNote->grand_total,
            'items' => $creditNote->relationLoaded('details')
                ? $creditNote->details->map(fn (CreditNoteDetail $d) => $this->detailResource($d))->values()->all()
                : [],
        ];
    }

    protected function toSelectOption(CreditNote $creditNote): array
    {
        return [
            'value' => $creditNote->uuid,
            'label' => $creditNote->credit_note_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?int $userId = null, ?CreditNote $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $today = Carbon::today()->toDateString();
        $creditNoteNumber = $data['creditNoteNumber'] ?? $existing?->credit_note_number;
        if ($creditNoteNumber === null || $creditNoteNumber === '') {
            $creditNoteNumber = $this->nextDocumentNumber(CreditNote::class, 'credit_note_number', 'CN', $organisationId);
        }

        return [
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId)
                ?? $existing?->customer_id,
            // The Add form has no salesman picker — the credit note is issued
            // by whoever is logged in, so default to the current user.
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId)
                ?? $existing?->salesman_id
                ?? $userId,
            'invoice_id' => $this->resolveInvoiceId($data['invoiceId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'payment_term_id' => $this->resolvePaymentTermId($data['paymentTermId'] ?? null, $organisationId),
            'reason' => $data['reason'] ?? $existing?->reason,
            'credit_note_number' => $creditNoteNumber,
            'credit_note_date' => $data['creditNoteDate'] ?? $existing?->credit_note_date?->toDateString() ?? $today,
            'credit_note_comment' => $data['notes'] ?? $existing?->credit_note_comment,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
            'source' => $existing?->source ?? 3,
            'approval_status' => $existing ? 'Updated' : 'Created',
        ];
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
            $computed['item_condition'] = (string) ($item['itemCondition'] ?? '1');
            $computed['batch_number'] = $item['batchNumber'] ?? null;
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
    protected function syncDetails(CreditNote $creditNote, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $creditNote->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $creditNote->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $creditNote->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(CreditNoteDetail $detail): array
    {
        return [
            'id' => (string) $detail->uuid,
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'itemName' => $detail->item?->item_name,
            'uom' => $detail->item_uom_id,
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
