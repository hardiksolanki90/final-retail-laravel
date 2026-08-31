<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository
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

    public function findByUuid(string $uuid, int $organisationId): Order
    {
        return Order::with(['details.item', 'customer', 'salesman', 'paymentTerm'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($data, $organisationId, $userId) {
            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $order = Order::create(array_merge(
                $this->headerAttributes($data, $organisationId, $userId),
                $totals,
            ));

            foreach ($lines as $line) {
                $order->details()->create($this->detailAttributes($line));
            }

            return $order->load(['details.item', 'customer', 'salesman', 'paymentTerm']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): Order
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $order = Order::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? [], $organisationId);
            $totals = $this->sumLineTotals($lines);

            $order->fill(array_merge(
                $this->headerAttributes($data, $organisationId, null, $order),
                $totals,
            ))->save();

            $this->syncDetails($order, $lines);

            return $order->fresh(['details.item', 'customer', 'salesman', 'paymentTerm']);
        });
    }

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $order = Order::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $order->details()->delete();
            $order->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = Order::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Order $order) {
                $order->details()->delete();
                $order->delete();
            }),
        };
    }

    public function toResource(Order $order): array
    {
        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'orderNumber' => $order->order_number,
            'orderDate' => $order->order_date?->toDateString(),
            'dueDate' => $order->due_date?->toDateString(),
            'deliveryDate' => $order->delivery_date?->toDateString(),
            'customerId' => $order->customer?->uuid,
            'salesmanId' => $order->salesman?->uuid,
            'paymentTermId' => $order->paymentTerm?->uuid,
            'routeId' => $order->route_id,
            'warehouseId' => $order->warehouse_id,
            'depotId' => $order->depot_id,
            'orderTypeId' => $order->order_type_id,
            'notes' => $order->any_comment,
            'currentStage' => $order->current_stage,
            'approvalStatus' => $order->approval_status,
            'status' => (bool) $order->status,
            'totalQty' => (float) $order->total_qty,
            'totalGross' => (float) $order->total_gross,
            'totalDiscountAmount' => (float) $order->total_discount_amount,
            'totalNet' => (float) $order->total_net,
            'totalVat' => (float) $order->total_vat,
            'totalExcise' => (float) $order->total_excise,
            'grandTotal' => (float) $order->grand_total,
            'items' => $order->relationLoaded('details')
                ? $order->details->map(fn (OrderDetail $d) => $this->detailResource($d))->values()->all()
                : [],
            'createdAt' => $order->created_at?->toISOString(),
            'updatedAt' => $order->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Order $order): array
    {
        return [
            'value' => $order->uuid,
            'label' => $order->order_number,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Order::with(['customer', 'salesman'])
            ->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('erp_number', 'like', "%{$search}%");
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
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, int $organisationId, ?int $userId = null, ?Order $existing = null): array
    {
        $today = Carbon::today()->toDateString();
        $orderNumber = $data['orderNumber'] ?? $existing?->order_number;
        if ($orderNumber === null || $orderNumber === '') {
            $orderNumber = $this->nextDocumentNumber(Order::class, 'order_number', 'ORD', $organisationId);
        }

        $attrs = [
            'organisation_id' => $organisationId,
            'customer_id' => $this->resolveCustomerId($data['customerId'] ?? null, $organisationId),
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'depot_id' => $this->resolveDepotId($data['depotId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId),
            'payment_term_id' => $this->resolvePaymentTermId(
                $data['paymentTermId'] ?? $data['paymentTerms'] ?? null,
                $organisationId,
            ),
            'reason_id' => $this->resolveReasonId($data['reasonId'] ?? null, $organisationId),
            'order_type_id' => (int) ($data['orderTypeId'] ?? $data['orderType'] ?? $existing?->order_type_id ?? 1),
            'storage_location_id' => (int) ($data['storageLocationId'] ?? $existing?->storage_location_id ?? 0),
            'lob_id' => isset($data['lobId']) ? (int) $data['lobId'] : $existing?->lob_id,
            'erp_number' => $data['erpNumber'] ?? $existing?->erp_number,
            'customer_lop' => $data['customerLop'] ?? $existing?->customer_lop,
            'order_number' => $orderNumber,
            'order_date' => $data['orderDate'] ?? $existing?->order_date?->toDateString() ?? $today,
            'due_date' => $data['dueDate'] ?? $existing?->due_date?->toDateString() ?? $today,
            'delivery_date' => $data['deliveryDate'] ?? $existing?->delivery_date?->toDateString(),
            'any_comment' => $data['notes'] ?? $data['anyComment'] ?? $existing?->any_comment,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
        ];

        if ($existing === null) {
            $attrs['source'] = (int) ($data['source'] ?? 3);
            $attrs['current_stage'] = $data['currentStage'] ?? 'Pending';
            $attrs['approval_status'] = $data['approvalStatus'] ?? 'Created';
            $attrs['order_status'] = 'created';
            $attrs['order_created_user_id'] = $userId;
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
            $computed['original_item_price'] = $computed['item_price'];
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
            'original_item_price' => $line['original_item_price'] ?? $line['item_price'],
            'order_status' => 'Pending',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(Order $order, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $order->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $order->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $order->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(OrderDetail $detail): array
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
            'orderStatus' => $detail->order_status,
        ];
    }
}
