<?php

namespace App\Repositories;

use App\Http\Requests\BulkSalesmanUnloadActionRequest;
use App\Http\Requests\StoreSalesmanUnloadRequest;
use App\Http\Requests\UpdateSalesmanUnloadRequest;
use App\Models\SalesmanUnload;
use App\Models\SalesmanUnloadDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesmanUnloadRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'salesman_id', 'status']);

        $paginated = $this->baseQuery($filters)
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (SalesmanUnload $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'salesmanUnloads'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'salesman_id', 'status']);

        $items = $this->baseQuery($filters)->orderByDesc('id')->get();

        return response()->json([
            'data' => $items->map(fn (SalesmanUnload $unload) => $this->toSelectOption($unload))->values(),
            'message' => 'Salesman unloads retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $unload = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($unload),
            'message' => 'Salesman unload retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanUnloadRequest $request): JsonResponse
    {
        $unload = $this->create($request->validated());

        return response()->json([
            'data' => $this->toResource($unload),
            'message' => 'Salesman unload created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanUnloadRequest $request): JsonResponse
    {
        $unload = $this->performUpdate($uuid, $request->validated());

        return response()->json([
            'data' => $this->toResource($unload),
            'message' => 'Salesman unload updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        DB::transaction(function () use ($request) {
            $unload = SalesmanUnload::where('uuid', (string) $request->input('id'))->firstOrFail();

            $unload->details()->delete();
            $unload->delete();
        });

        return response()->json(['message' => 'Salesman unload deleted successfully.']);
    }

    public function bulkAction(BulkSalesmanUnloadActionRequest $request): JsonResponse
    {
        $query = SalesmanUnload::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (SalesmanUnload $unload) {
                $unload->details()->delete();
                $unload->delete();
            }),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): SalesmanUnload
    {
        return SalesmanUnload::with(['details.item', 'salesman', 'van', 'warehouse', 'route'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function create(array $data): SalesmanUnload
    {
        return DB::transaction(function () use ($data) {
            // ResolvesDocumentRelations (shared with repositories outside
            // this migration) still needs an explicit organisation id to
            // resolve related records and generate document numbers.
            $organisationId = (int) Auth::user()->organisation_id;

            $unload = SalesmanUnload::create($this->headerAttributes($data, $organisationId));

            foreach ($this->mapLines($data['items'] ?? [], $organisationId) as $line) {
                $unload->details()->create($this->detailAttributes($line));
            }

            return $unload->load(['details.item', 'salesman', 'van', 'warehouse', 'route']);
        });
    }

    protected function performUpdate(string $uuid, array $data): SalesmanUnload
    {
        return DB::transaction(function () use ($uuid, $data) {
            $unload = SalesmanUnload::where('uuid', $uuid)->firstOrFail();

            // ResolvesDocumentRelations (shared with repositories outside
            // this migration) still needs an explicit organisation id to
            // resolve related records and generate document numbers.
            $organisationId = (int) Auth::user()->organisation_id;

            $unload->fill($this->headerAttributes($data, $organisationId, $unload))->save();
            $this->syncDetails($unload, $this->mapLines($data['items'] ?? [], $organisationId));

            return $unload->fresh(['details.item', 'salesman', 'van', 'warehouse', 'route']);
        });
    }

    public function toResource(SalesmanUnload $unload): array
    {
        return [
            'id' => $unload->id,
            'uuid' => $unload->uuid,
            'unloadNumber' => $unload->unload_number,
            'routeId' => $unload->route?->uuid,
            'routeName' => $unload->route?->route_name,
            'warehouseId' => $unload->warehouse?->uuid,
            'warehouseName' => $unload->warehouse?->name,
            'vanId' => $unload->van?->uuid,
            'vanCode' => $unload->van?->van_code,
            'salesmanId' => (string) $unload->salesman_id,
            'salesmanName' => $unload->salesman
                ? trim($unload->salesman->firstname . ' ' . $unload->salesman->lastname)
                : null,
            'transactionDate' => $unload->transaction_date?->toDateString(),
            'status' => (bool) $unload->status,
            'approvalStatus' => $unload->approval_status,
            'items' => $unload->relationLoaded('details')
                ? $unload->details->map(fn(SalesmanUnloadDetail $d) => $this->detailResource($d))->values()->all()
                : [],

        ];
    }

    public function toSelectOption(SalesmanUnload $unload): array
    {
        return [
            'value' => $unload->uuid,
            'label' => $unload->unload_number,
        ];
    }

    /**
     * Filterable::scopeFilter (via ::filter()) handles the search on
     * unload_number and the boolean status filter from the model's
     * $searchable / $filterable. salesman_id is applied manually below —
     * see the note on SalesmanUnload::$filterable.
     */
    protected function baseQuery(array $filters): Builder
    {
        $query = SalesmanUnload::with(['salesman', 'van', 'warehouse'])->filter($filters);

        if (! empty($filters['salesman_id'])) {
            $salesmanId = $this->resolveSalesmanId($filters['salesman_id'], (int) Auth::user()->organisation_id);
            if ($salesmanId) {
                $query->where('salesman_id', $salesmanId);
            }
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, int $organisationId, ?SalesmanUnload $existing = null): array
    {
        $today = Carbon::today()->toDateString();
        $unloadNumber = $data['unloadNumber'] ?? $existing?->unload_number;
        if ($unloadNumber === null || $unloadNumber === '') {
            $unloadNumber = $this->nextDocumentNumber(SalesmanUnload::class, 'unload_number', 'SU', $organisationId);
        }

        $attrs = [
            'unload_number' => $unloadNumber,
            'route_id' => $this->resolveRouteId($data['routeId'] ?? null, $organisationId) ?? $existing?->route_id,
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId) ?? $existing?->warehouse_id,
            'van_id' => $this->resolveVanId($data['vanId'] ?? null, $organisationId) ?? $existing?->van_id,
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId) ?? $existing?->salesman_id,
            'transaction_date' => $data['transactionDate'] ?? $existing?->transaction_date?->toDateString() ?? $today,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
        ];

        if ($existing === null) {
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
            $itemId = $this->resolveItemId($item['itemId'] ?? null, $organisationId);
            if (! $itemId) {
                continue;
            }

            $lines[] = [
                'uuid' => ! empty($item['uuid']) ? (string) $item['uuid'] : (string) Str::uuid(),
                'item_id' => $itemId,
                'item_uom_id' => $this->resolveItemUomId($item['uom'] ?? $item['itemUomId'] ?? null, $organisationId),
                'unload_qty' => (float) ($item['quantity'] ?? $item['unloadQty'] ?? 0),
                'unload_type' => $item['unloadType'] ?? 'fresh',
                'reason_id' => $this->resolveReasonId($item['reasonId'] ?? null, $organisationId),
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
            'item_uom_id' => $line['item_uom_id'],
            'unload_qty' => $line['unload_qty'],
            'unload_type' => $line['unload_type'],
            'reason_id' => $line['reason_id'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(SalesmanUnload $unload, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $unload->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $unload->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $unload->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(SalesmanUnloadDetail $detail): array
    {
        return [
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'itemName' => $detail->item?->item_name,
            'uom' => $detail->item_uom_id,
            'quantity' => (float) $detail->unload_qty,
            'unloadType' => $detail->unload_type,
            'reasonId' => $detail->reason?->uuid,
        ];
    }
}
