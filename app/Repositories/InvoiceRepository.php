<?php

namespace App\Repositories;

use App\Http\Requests\BulkInvoiceActionRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = Invoice::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman', 'order'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Invoice $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'invoices'), 200);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Invoice::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman', 'order'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Invoice $item) => $this->toSelectOption($item))->values(),
            'message' => 'Invoices retrieved successfully.',
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $invoice = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($invoice),
            'message' => 'Invoice retrieved successfully.',
        ], 200);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invoice = DB::transaction(function () use ($data, $request) {
            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $invoice = Invoice::create(array_merge(
                $this->headerAttributes($data, $request->user()->id),
                $totals,
            ));

            foreach ($lines as $line) {
                $invoice->details()->create($this->detailAttributes($line));
            }

            return $invoice->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery']);
        });

        return response()->json([
            'data' => $this->toResource($invoice),
            'message' => 'Invoice created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invoice = DB::transaction(function () use ($uuid, $data) {
            $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $invoice->fill(array_merge(
                $this->headerAttributes($data, null, $invoice),
                $totals,
            ))->save();

            $this->syncDetails($invoice, $lines);

            return $invoice->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery']);
        });

        return response()->json([
            'data' => $this->toResource($invoice),
            'message' => 'Invoice updated successfully.',
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $uuid = (string) $request->input('id');

        DB::transaction(function () use ($uuid) {
            $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

            $invoice->details()->delete();
            $invoice->delete();
        });

        return response()->json(['message' => 'Invoice deleted successfully.'], 200);
    }

    public function bulkAction(BulkInvoiceActionRequest $request): JsonResponse
    {
        $query = Invoice::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Invoice $invoice) {
                $invoice->details()->delete();
                $invoice->delete();
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

        $filters = $request->only(['search', 'customer_id', 'salesman_id', 'current_stage', 'status', 'date_from', 'date_to']);

        if (! empty($filters['customer_id'])) {
            $filters['customer_id'] = $this->resolveCustomerId($filters['customer_id'], $organisationId);
        }

        if (! empty($filters['salesman_id'])) {
            $filters['salesman_id'] = $this->resolveSalesmanId($filters['salesman_id'], $organisationId);
        }

        return $filters;
    }

    protected function findByUuid(string $uuid): Invoice
    {
        return Invoice::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'uuid' => $invoice->uuid,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoice->invoice_date?->toDateString(),
            'dueDate' => $invoice->invoice_due_date?->toDateString(),
            'customerId' => $invoice->customer?->uuid,
            'customerName' => $invoice->customer?->shop_name
                ?: trim(($invoice->customer?->firstname ?? '').' '.($invoice->customer?->lastname ?? '')),
            'orderId' => $invoice->order?->uuid,
            'orderNumber' => $invoice->order?->order_number,
            'deliveryId' => $invoice->delivery?->uuid,
            'salesmanId' => $invoice->salesman?->uuid,
            'paymentTermId' => $invoice->paymentTerm?->uuid,
            'vanId' => $invoice->van_id,
            'routeId' => $invoice->route_id,
            'warehouseId' => $invoice->warehouse_id,
            'depotId' => $invoice->depot_id,
            'orderTypeId' => $invoice->order_type_id,
            'invoiceType' => $invoice->invoice_type,
            'currentStage' => $invoice->current_stage,
            'approvalStatus' => $invoice->approval_status,
            'status' => (bool) $invoice->status,
            'totalQty' => (float) $invoice->total_qty,
            'totalGross' => (float) $invoice->total_gross,
            'totalDiscountAmount' => (float) $invoice->total_discount_amount,
            'totalNet' => (float) $invoice->total_net,
            'totalVat' => (float) $invoice->total_vat,
            'totalExcise' => (float) $invoice->total_excise,
            'grandTotal' => (float) $invoice->grand_total,
            'items' => $invoice->relationLoaded('details')
                ? $invoice->details->map(fn (InvoiceDetail $d) => $this->detailResource($d))->values()->all()
                : [],
        ];
    }

    protected function toSelectOption(Invoice $invoice): array
    {
        return [
            'value' => $invoice->uuid,
            'label' => $invoice->invoice_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?int $userId = null, ?Invoice $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $today = Carbon::today()->toDateString();
        $invoiceNumber = $data['invoiceNumber'] ?? $existing?->invoice_number;
        if ($invoiceNumber === null || $invoiceNumber === '') {
            $invoiceNumber = $this->nextDocumentNumber(Invoice::class, 'invoice_number', 'INV', $organisationId);
        }

        $invoiceDate = $data['invoiceDate'] ?? $existing?->invoice_date?->toDateString() ?? $today;
        $dueDate = $data['dueDate'] ?? $data['invoiceDueDate'] ?? $existing?->invoice_due_date?->toDateString() ?? $invoiceDate;

        $invoiceType = $data['invoiceType'] ?? $data['invoiceTypeId'] ?? $existing?->invoice_type ?? '1';
        $invoiceType = (string) $invoiceType;

        $attrs = [
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId),
            'order_id' => $this->resolveOrderId($data['orderId'] ?? null, $organisationId),
            'delivery_id' => $this->resolveDeliveryId($data['deliveryId'] ?? null, $organisationId),
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'depot_id' => $this->resolveDepotId($data['depotId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId),
            'van_id' => $this->resolveVanId($data['vanId'] ?? null, $organisationId),
            'payment_term_id' => $this->resolvePaymentTermId(
                $data['paymentTermId'] ?? $data['paymentTerms'] ?? null,
                $organisationId,
            ),
            'reason_id' => $this->resolveReasonId($data['reasonId'] ?? null, $organisationId),
            'order_type_id' => (int) ($data['orderTypeId'] ?? $data['orderType'] ?? $existing?->order_type_id ?? 1),
            'invoice_type' => $invoiceType,
            'storage_location_id' => isset($data['storageLocationId'])
                ? (int) $data['storageLocationId']
                : $existing?->storage_location_id,
            'lob_id' => isset($data['lobId']) ? (int) $data['lobId'] : $existing?->lob_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'invoice_due_date' => $dueDate,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
        ];

        if ($existing === null) {
            $attrs['source'] = (int) ($data['source'] ?? 3);
            $attrs['current_stage'] = $data['currentStage'] ?? 'Pending';
            $attrs['approval_status'] = $data['approvalStatus'] ?? 'Created';
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

            $computed['original_item_qty'] = $computed['item_qty'];
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
            'item_qty' => $line['item_qty'],
            'item_price' => $line['item_price'],
            'item_gross' => $line['item_gross'],
            'item_discount_amount' => $line['item_discount_amount'],
            'item_net' => $line['item_net'],
            'item_vat' => $line['item_vat'],
            'item_excise' => $line['item_excise'],
            'item_grand_total' => $line['item_grand_total'],
            'original_item_qty' => $line['original_item_qty'] ?? $line['item_qty'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(Invoice $invoice, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $invoice->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $invoice->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $invoice->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(InvoiceDetail $detail): array
    {
        return [
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'itemUomId' => $detail->item_uom_id,
            'quantity' => (float) $detail->item_qty,
            'price' => (float) $detail->item_price,
            'discount' => (float) $detail->item_discount_amount,
            'vat' => (float) $detail->item_vat,
            'excise' => (float) $detail->item_excise,
            'net' => (float) $detail->item_net,
            'total' => (float) $detail->item_grand_total,
        ];
    }
}
