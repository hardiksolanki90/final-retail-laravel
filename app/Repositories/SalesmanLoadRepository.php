<?php

namespace App\Repositories;

use App\Models\SalesmanLoad;
use App\Models\SalesmanLoadDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesmanLoadRepository
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

    public function findByUuid(string $uuid, int $organisationId): SalesmanLoad
    {
        return SalesmanLoad::with(['details.item', 'salesman', 'van', 'warehouse', 'depot', 'route'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId, ?int $userId = null): SalesmanLoad
    {
        return DB::transaction(function () use ($data, $organisationId) {
            $header = $this->headerAttributes($data, $organisationId);
            $load = SalesmanLoad::create($header);

            $lines = $this->mapLines($data['items'] ?? [], $organisationId, $header);
            foreach ($lines as $line) {
                $load->details()->create($this->detailAttributes($line));
            }

            return $load->load(['details.item', 'salesman', 'van', 'warehouse', 'depot', 'route']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): SalesmanLoad
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $load = SalesmanLoad::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

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

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $load = SalesmanLoad::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $load->details()->delete();
            $load->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = SalesmanLoad::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (SalesmanLoad $load) {
                $load->details()->delete();
                $load->delete();
            }),
        };
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
            'createdAt' => $load->created_at?->toISOString(),
            'updatedAt' => $load->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(SalesmanLoad $load): array
    {
        return [
            'value' => $load->uuid,
            'label' => $load->load_number,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = SalesmanLoad::with(['salesman', 'van'])
            ->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('load_number', 'like', "%{$search}%");
        }

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

        if (! empty($filters['date_from'])) {
            $query->whereDate('load_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('load_date', '<=', $filters['date_to']);
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
            'organisation_id' => $organisationId,
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
