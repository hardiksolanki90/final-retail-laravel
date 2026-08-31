<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\DeliveryDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeliveryRepository
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

    public function findByUuid(string $uuid, int $organisationId): Delivery
    {
        return Delivery::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'order'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId, ?int $userId = null): Delivery
    {
        return DB::transaction(function () use ($data, $organisationId, $userId) {
            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $delivery = Delivery::create(array_merge(
                $this->headerAttributes($data, $organisationId, $userId),
                $totals,
            ));

            foreach ($lines as $line) {
                $delivery->details()->create($this->detailAttributes($line));
            }

            return $delivery->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'order']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): Delivery
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $delivery = Delivery::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $delivery->fill(array_merge(
                $this->headerAttributes($data, $organisationId, null, $delivery),
                $totals,
            ))->save();

            $this->syncDetails($delivery, $lines);

            return $delivery->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'order']);
        });
    }

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $delivery = Delivery::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $delivery->details()->delete();
            $delivery->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = Delivery::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Delivery $delivery) {
                $delivery->details()->delete();
                $delivery->delete();
            }),
        };
    }

    public function toResource(Delivery $delivery): array
    {
        return [
            'id' => $delivery->id,
            'uuid' => $delivery->uuid,
            'deliveryNumber' => $delivery->delivery_number,
            'deliveryDate' => $delivery->delivery_date?->toDateString(),
            'deliveryTime' => $delivery->delivery_time,
            'dueDate' => $delivery->delivery_due_date?->toDateString(),
            'customerId' => $delivery->customer?->uuid,
            'orderId' => $delivery->order?->uuid,
            'salesmanId' => $delivery->salesman?->uuid,
            'paymentTermId' => $delivery->paymentTerm?->uuid,
            'routeId' => $delivery->route_id,
            'warehouseId' => $delivery->warehouse_id,
            'deliveryType' => $delivery->delivery_type,
            'notes' => $delivery->current_stage_comment,
            'currentStage' => $delivery->current_stage,
            'approvalStatus' => $delivery->approval_status,
            'status' => (bool) $delivery->status,
            'totalQty' => (float) $delivery->total_qty,
            'totalGross' => (float) $delivery->total_gross,
            'totalDiscountAmount' => (float) $delivery->total_discount_amount,
            'totalNet' => (float) $delivery->total_net,
            'totalVat' => (float) $delivery->total_vat,
            'totalExcise' => (float) $delivery->total_excise,
            'grandTotal' => (float) $delivery->grand_total,
            'items' => $delivery->relationLoaded('details')
                ? $delivery->details->map(fn (DeliveryDetail $d) => $this->detailResource($d))->values()->all()
                : [],
            'createdAt' => $delivery->created_at?->toISOString(),
            'updatedAt' => $delivery->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Delivery $delivery): array
    {
        return [
            'value' => $delivery->uuid,
            'label' => $delivery->delivery_number,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Delivery::with(['customer', 'salesman'])
            ->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
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
            $query->whereDate('delivery_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('delivery_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, int $organisationId, ?int $userId = null, ?Delivery $existing = null): array
    {
        $today = Carbon::today()->toDateString();
        $deliveryNumber = $data['deliveryNumber'] ?? $existing?->delivery_number;
        if ($deliveryNumber === null || $deliveryNumber === '') {
            $deliveryNumber = $this->nextDocumentNumber(Delivery::class, 'delivery_number', 'DEL', $organisationId);
        }

        $deliveryDate = $data['deliveryDate'] ?? $existing?->delivery_date?->toDateString() ?? $today;
        $dueDate = $data['dueDate'] ?? $data['deliveryDueDate'] ?? $existing?->delivery_due_date?->toDateString() ?? $deliveryDate;

        $attrs = [
            'organisation_id' => $organisationId,
            'order_id' => $this->resolveOrderId($data['orderId'] ?? null, $organisationId),
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId),
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId),
            'payment_term_id' => $this->resolvePaymentTermId(
                $data['paymentTermId'] ?? $data['paymentTerms'] ?? null,
                $organisationId,
            ),
            'reason_id' => $this->resolveReasonId($data['reasonId'] ?? null, $organisationId),
            'delivery_type' => (int) ($data['deliveryTypeId'] ?? $data['deliveryType'] ?? $existing?->delivery_type ?? 1),
            'delivery_type_source' => (string) ($data['deliveryTypeSource'] ?? $existing?->delivery_type_source ?? '1'),
            'storage_location_id' => isset($data['storageLocationId'])
                ? (int) $data['storageLocationId']
                : $existing?->storage_location_id,
            'lob_id' => isset($data['lobId']) ? (int) $data['lobId'] : $existing?->lob_id,
            'delivery_number' => $deliveryNumber,
            'delivery_date' => $deliveryDate,
            'delivery_time' => $data['deliveryTime'] ?? $existing?->delivery_time ?? '00:00:00',
            'delivery_due_date' => $dueDate,
            'current_stage_comment' => $data['notes'] ?? $data['currentStageComment'] ?? $existing?->current_stage_comment,
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

            $computed['open_qty'] = $computed['item_qty'];
            $computed['original_item_qty'] = $computed['item_qty'];
            $computed['original_item_uom_id'] = $computed['item_uom_id'] ?: null;
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
            'original_item_uom_id' => $line['original_item_uom_id'] ?? null,
            'reason_id' => $line['reason_id'] ?? null,
            'item_qty' => $line['item_qty'],
            'item_price' => $line['item_price'],
            'item_gross' => $line['item_gross'],
            'item_discount_amount' => $line['item_discount_amount'],
            'item_net' => $line['item_net'],
            'item_vat' => $line['item_vat'],
            'item_excise' => $line['item_excise'],
            'item_grand_total' => $line['item_grand_total'],
            'open_qty' => $line['open_qty'] ?? $line['item_qty'],
            'original_item_qty' => $line['original_item_qty'] ?? $line['item_qty'],
            'delivery_status' => 'Pending',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(Delivery $delivery, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $delivery->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $delivery->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $delivery->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(DeliveryDetail $detail): array
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
            'deliveryStatus' => $detail->delivery_status,
        ];
    }
}
