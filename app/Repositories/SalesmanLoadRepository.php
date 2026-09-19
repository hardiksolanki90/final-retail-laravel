<?php

namespace App\Repositories;

use App\Http\Requests\BulkSalesmanLoadActionRequest;
use App\Http\Requests\StoreSalesmanLoadRequest;
use App\Http\Requests\UpdateSalesmanLoadRequest;
use App\Models\SalesmanLoad;
use App\Models\SalesmanLoadDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesmanLoadRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'salesman_id', 'status', 'date_from', 'date_to']);

        $paginated = $this->baseQuery($filters)
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (SalesmanLoad $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'salesmanLoads'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'salesman_id', 'status', 'date_from', 'date_to']);

        $items = $this->baseQuery($filters)->orderByDesc('id')->get();

        return response()->json([
            'data' => $items->map(fn (SalesmanLoad $load) => $this->toSelectOption($load))->values(),
            'message' => 'Salesman loads retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $load = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($load),
            'message' => 'Salesman load retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanLoadRequest $request): JsonResponse
    {
        $load = $this->create($request->validated(), $request->user()->id);

        return response()->json([
            'data' => $this->toResource($load),
            'message' => 'Salesman load created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanLoadRequest $request): JsonResponse
    {
        $load = $this->performUpdate($uuid, $request->validated());

        return response()->json([
            'data' => $this->toResource($load),
            'message' => 'Salesman load updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        DB::transaction(function () use ($request) {
            $load = SalesmanLoad::where('uuid', (string) $request->input('id'))->firstOrFail();

            $load->details()->delete();
            $load->delete();
        });

        return response()->json(['message' => 'Salesman load deleted successfully.']);
    }

    public function bulkAction(BulkSalesmanLoadActionRequest $request): JsonResponse
    {
        $query = SalesmanLoad::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (SalesmanLoad $load) {
                $load->details()->delete();
                $load->delete();
            }),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): SalesmanLoad
    {
        return SalesmanLoad::with(['details.item', 'salesman', 'van', 'warehouse', 'depot', 'route'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function create(array $data, ?int $userId = null): SalesmanLoad
    {
        return DB::transaction(function () use ($data) {
            // ResolvesDocumentRelations (shared with repositories outside
            // this migration) still needs an explicit organisation id to
            // resolve related records and generate document numbers.
            $organisationId = (int) Auth::user()->organisation_id;

            $header = $this->headerAttributes($data, $organisationId);
            $load = SalesmanLoad::create($header);

            $lines = $this->mapLines($data['items'] ?? [], $organisationId, $header);
            foreach ($lines as $line) {
                $load->details()->create($this->detailAttributes($line));
            }

            return $load->load(['details.item', 'salesman', 'van', 'warehouse', 'depot', 'route']);
        });
    }

    protected function performUpdate(string $uuid, array $data): SalesmanLoad
    {
        return DB::transaction(function () use ($uuid, $data) {
            $load = SalesmanLoad::where('uuid', $uuid)->firstOrFail();

            // ResolvesDocumentRelations (shared with repositories outside
            // this migration) still needs an explicit organisation id to
            // resolve related records and generate document numbers.
            $organisationId = (int) Auth::user()->organisation_id;

            $header = $this->headerAttributes($data, $organisationId, $load);
            $load->fill($header)->save();

            $lines = $this->mapLines($data['items'] ?? [], $organisationId, array_merge($header, [
                'salesman_id' => $load->salesman_id,
                'van_id' => $load->van_id,
                'warehouse_id' => $load->warehouse_id,
                'route_id' => $load->route_id,
                'depot_id' => $load->depot_id,
                'load_date' => $load->load_date?->toDateString() ?? $header['load_date'],
            ]));
            $this->syncDetails($load, $lines);

            return $load->fresh(['details.item', 'salesman', 'van', 'warehouse', 'depot', 'route']);
        });
    }

    public function toResource(SalesmanLoad $load): array
    {
        return [
            'id' => $load->id,
            'uuid' => $load->uuid,
            'loadNumber' => $load->load_number,
            'loadCode' => $load->load_number,
            'loadDate' => $load->load_date?->toDateString(),
            'salesmanId' => $load->salesman?->uuid,
            'vanId' => $load->van?->uuid ?? $load->van_id,
            'warehouseId' => $load->warehouse?->uuid ?? $load->warehouse_id,
            'depotId' => $load->depot?->uuid ?? $load->depot_id,
            'routeId' => $load->route?->uuid ?? $load->route_id,
            'orderId' => $load->order_id,
            'deliveryId' => $load->delivery_id,
            'tripNumber' => $load->trip_number,
            'loadConfirm' => (bool) $load->load_confirm,
            'status' => $load->load_confirm ? 'loaded' : 'pending',
            'approvalStatus' => $load->approval_status,
            'active' => (bool) $load->status,
            'items' => $load->relationLoaded('details')
                ? $load->details->map(fn (SalesmanLoadDetail $d) => $this->detailResource($d))->values()->all()
                : [],
        ];
    }

    public function toSelectOption(SalesmanLoad $load): array
    {
        return [
            'value' => $load->uuid,
            'label' => $load->load_number,
        ];
    }

    /**
     * Filterable::scopeFilter (via ::filter()) handles the search on
     * load_number and the load_date date-range (date_from/date_to) from the
     * model's $searchable / $dateRangeColumn. salesman_id and status are
     * applied manually below — see the note on SalesmanLoad::$filterable.
     */
    protected function baseQuery(array $filters): Builder
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $query = SalesmanLoad::with(['salesman', 'van'])->filter($filters);

        if (! empty($filters['salesman_id'])) {
            $salesmanId = $this->resolveSalesmanId($filters['salesman_id'], $organisationId);
            if ($salesmanId) {
                $query->where('salesman_id', $salesmanId);
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = $filters['status'];
            if (is_string($status) && in_array(strtolower($status), ['pending', 'loaded'], true)) {
                $query->where('load_confirm', strtolower($status) === 'loaded');
            } else {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            }
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, int $organisationId, ?SalesmanLoad $existing = null): array
    {
        $today = Carbon::today()->toDateString();
        $loadNumber = $data['loadCode'] ?? $data['loadNumber'] ?? $existing?->load_number;
        if ($loadNumber === null || $loadNumber === '') {
            $loadNumber = $this->nextDocumentNumber(SalesmanLoad::class, 'load_number', 'SL', $organisationId);
        }

        $attrs = [
            'load_number' => $loadNumber,
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'van_id' => $this->resolveVanId($data['vanId'] ?? null, $organisationId),
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId),
            'depot_id' => $this->resolveDepotId($data['depotId'] ?? null, $organisationId),
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId),
            'order_id' => $this->resolveOrderId($data['orderId'] ?? null, $organisationId),
            'delivery_id' => $this->resolveDeliveryId($data['deliveryId'] ?? null, $organisationId),
            'load_date' => $data['loadDate'] ?? $existing?->load_date?->toDateString() ?? $today,
            'load_confirm' => $this->resolveLoadConfirm($data, $existing),
            'status' => (bool) ($data['active'] ?? $data['status'] ?? $existing?->status ?? true),
            'trip_number' => (int) ($data['tripNumber'] ?? $existing?->trip_number ?? 1),
        ];

        // If status was pending|loaded string, don't treat it as boolean status
        if (isset($data['status']) && is_string($data['status']) && in_array(strtolower($data['status']), ['pending', 'loaded'], true)) {
            $attrs['status'] = (bool) ($data['active'] ?? $existing?->status ?? true);
        }

        if ($existing === null) {
            $attrs['approval_status'] = $data['approvalStatus'] ?? 'Created';
        }

        return $attrs;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveLoadConfirm(array $data, ?SalesmanLoad $existing = null): bool
    {
        if (array_key_exists('loadConfirm', $data)) {
            return (bool) $data['loadConfirm'];
        }

        if (isset($data['status']) && is_string($data['status'])) {
            $status = strtolower($data['status']);
            if ($status === 'pending') {
                return false;
            }
            if ($status === 'loaded') {
                return true;
            }
        }

        return (bool) ($existing?->load_confirm ?? true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $header
     * @return array<int, array<string, mixed>>
     */
    protected function mapLines(array $items, int $organisationId, array $header): array
    {
        $lines = [];

        foreach ($items as $item) {
            $itemId = $this->resolveItemId($item['itemId'] ?? null, $organisationId);
            if (! $itemId) {
                continue;
            }

            $uom = $item['uom'] ?? $item['itemUom'] ?? $item['item_uom'] ?? '';
            $qty = $item['quantity'] ?? $item['loadQty'] ?? $item['load_qty'] ?? 0;

            $lines[] = [
                'uuid' => ! empty($item['uuid']) ? (string) $item['uuid'] : (string) Str::uuid(),
                'item_id' => $itemId,
                'item_uom' => (string) $uom,
                'load_qty' => (string) $qty,
                'lower_qty' => (float) ($item['lowerQty'] ?? $item['lower_qty'] ?? 0),
                'ctn_qty' => (float) ($item['ctnQty'] ?? $item['ctn_qty'] ?? 0),
                'requested_qty' => (float) ($item['requestedQty'] ?? $item['requested_qty'] ?? 0),
                'load_date' => $header['load_date'],
                'salesman_id' => $header['salesman_id'] ?? null,
                'van_id' => $header['van_id'] ?? null,
                'warehouse_id' => $header['warehouse_id'] ?? null,
                'route_id' => $header['route_id'] ?? null,
                'depot_id' => $header['depot_id'] ?? null,
            ];
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
            'item_uom' => $line['item_uom'] ?? '',
            'load_qty' => $line['load_qty'] ?? '0',
            'lower_qty' => $line['lower_qty'] ?? 0,
            'ctn_qty' => $line['ctn_qty'] ?? 0,
            'requested_qty' => $line['requested_qty'] ?? 0,
            'load_date' => $line['load_date'],
            'salesman_id' => $line['salesman_id'] ?? null,
            'van_id' => $line['van_id'] ?? null,
            'warehouse_id' => $line['warehouse_id'] ?? null,
            'route_id' => $line['route_id'] ?? null,
            'depot_id' => $line['depot_id'] ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(SalesmanLoad $load, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $load->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $load->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $load->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(SalesmanLoadDetail $detail): array
    {
        return [
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'uom' => $detail->item_uom,
            'itemUom' => $detail->item_uom,
            'quantity' => $detail->load_qty,
            'loadQty' => $detail->load_qty,
            'lowerQty' => (float) $detail->lower_qty,
            'ctnQty' => (float) $detail->ctn_qty,
            'requestedQty' => (float) $detail->requested_qty,
            'loadDate' => $detail->load_date?->toDateString(),
        ];
    }
}
