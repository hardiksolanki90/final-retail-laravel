<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    use ResolvesDocumentRelations;

    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Invoice
    {
        return Invoice::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($data, $organisationId, $userId) {
            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $invoice = Invoice::create(array_merge(
                $this->headerAttributes($data, $organisationId, $userId),
                $totals,
            ));

            foreach ($lines as $line) {
                $invoice->details()->create($this->detailAttributes($line));
            }

            return $invoice->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): Invoice
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $invoice = Invoice::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $invoice->fill(array_merge(
                $this->headerAttributes($data, $organisationId, null, $invoice),
                $totals,
            ))->save();

            $this->syncDetails($invoice, $lines);

            return $invoice->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'order', 'delivery']);
        });
    }

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $invoice = Invoice::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $invoice->details()->delete();
            $invoice->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = Invoice::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Invoice $invoice) {
                $invoice->details()->delete();
                $invoice->delete();
            }),
        };
    }

    public function toResource(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'uuid' => $invoice->uuid,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoice->invoice_date?->toDateString(),
            'dueDate' => $invoice->invoice_due_date?->toDateString(),
            'customerId' => $invoice->customer?->uuid,
            'orderId' => $invoice->order?->uuid,
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
            'createdAt' => $invoice->created_at?->toISOString(),
            'updatedAt' => $invoice->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Invoice $invoice): array
    {
        return [
            'value' => $invoice->uuid,
            'label' => $invoice->invoice_number,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Invoice::with(['customer', 'salesman'])
            ->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_lpo', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['customer_id'])) {
            $customerId = $this->resolveCustomerId($filters['customer_id'], $organisationId);
            if ($customerId) {
                $query->where('customer_id', $customerId);
            }
        }

        if (! empty($filters['salesman_id'])) {
            $salesmanId = $this->resolveSalesmanId($filters['salesman_id'], $organisationId);
            if ($salesmanId) {
                $query->where('salesman_id', $salesmanId);
            }
        }

        if (! empty($filters['current_stage'])) {
            $query->where('current_stage', $filters['current_stage']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, int $organisationId, ?int $userId = null, ?Invoice $existing = null): array
    {
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
            'organisation_id' => $organisationId,
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
    protected function mapLines(array $items, int $organisationId): array
    {
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
