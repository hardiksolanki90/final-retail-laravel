<?php

namespace App\Repositories;

use App\Http\Requests\BulkDeliveryActionRequest;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = Delivery::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Delivery $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'deliveries'), 200);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Delivery::filter($this->resolvedFilters($request))
            ->with(['customer', 'salesman'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Delivery $item) => $this->toSelectOption($item))->values(),
            'message' => 'Deliveries retrieved successfully.',
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $delivery = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($delivery),
            'message' => 'Delivery retrieved successfully.',
        ], 200);
    }

    public function store(StoreDeliveryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $delivery = DB::transaction(function () use ($data, $request) {
            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $delivery = Delivery::create(array_merge(
                $this->headerAttributes($data, $request->user()->id),
                $totals,
            ));

            foreach ($lines as $line) {
                $delivery->details()->create($this->detailAttributes($line));
            }

            return $delivery->load(['details.item', 'customer', 'salesman', 'paymentTerm', 'order']);
        });

        return response()->json([
            'data' => $this->toResource($delivery),
            'message' => 'Delivery created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDeliveryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $delivery = DB::transaction(function () use ($uuid, $data) {
            $delivery = Delivery::where('uuid', $uuid)->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? []);
            $totals = $this->sumLineTotals($lines);

            $delivery->fill(array_merge(
                $this->headerAttributes($data, null, $delivery),
                $totals,
            ))->save();

            $this->syncDetails($delivery, $lines);

            return $delivery->fresh(['details.item', 'customer', 'salesman', 'paymentTerm', 'order']);
        });

        return response()->json([
            'data' => $this->toResource($delivery),
            'message' => 'Delivery updated successfully.',
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $uuid = (string) $request->input('id');

        DB::transaction(function () use ($uuid) {
            $delivery = Delivery::where('uuid', $uuid)->firstOrFail();

            $delivery->details()->delete();
            $delivery->delete();
        });

        return response()->json(['message' => 'Delivery deleted successfully.'], 200);
    }

    public function bulkAction(BulkDeliveryActionRequest $request): JsonResponse
    {
        $query = Delivery::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (Delivery $delivery) {
                $delivery->details()->delete();
                $delivery->delete();
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

    protected function findByUuid(string $uuid): Delivery
    {
        return Delivery::with(['details.item', 'customer', 'salesman', 'paymentTerm', 'order'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(Delivery $delivery): array
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
        ];
    }

    protected function toSelectOption(Delivery $delivery): array
    {
        return [
            'value' => $delivery->uuid,
            'label' => $delivery->delivery_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?int $userId = null, ?Delivery $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $today = Carbon::today()->toDateString();
        $deliveryNumber = $data['deliveryNumber'] ?? $existing?->delivery_number;
        if ($deliveryNumber === null || $deliveryNumber === '') {
            $deliveryNumber = $this->nextDocumentNumber(Delivery::class, 'delivery_number', 'DEL', $organisationId);
        }

        $deliveryDate = $data['deliveryDate'] ?? $existing?->delivery_date?->toDateString() ?? $today;
        $dueDate = $data['dueDate'] ?? $data['deliveryDueDate'] ?? $existing?->delivery_due_date?->toDateString() ?? $deliveryDate;

        $attrs = [
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
    protected function mapLines(array $items): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

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
