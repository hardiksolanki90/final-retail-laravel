<?php

namespace App\Repositories;

use App\Http\Requests\BulkGoodReceiptNoteActionRequest;
use App\Http\Requests\StoreGoodReceiptNoteRequest;
use App\Http\Requests\UpdateGoodReceiptNoteRequest;
use App\Models\GoodReceiptNote;
use App\Models\GoodReceiptNoteDetail;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoodReceiptNoteRepository
{
    use ResolvesDocumentRelations;

    public function list(Request $request): JsonResponse
    {
        $paginated = GoodReceiptNote::filter($request->only(['search', 'status', 'date_from', 'date_to']))
            ->with(['sourceWarehouse', 'destinationWarehouse'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (GoodReceiptNote $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'goodReceiptNotes'), 200);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        $items = GoodReceiptNote::filter($request->only(['search', 'status', 'date_from', 'date_to']))
            ->with(['sourceWarehouse', 'destinationWarehouse'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (GoodReceiptNote $item) => $this->toSelectOption($item))->values(),
            'message' => 'Goods receipt notes retrieved successfully.',
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $grn = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($grn),
            'message' => 'Goods receipt note retrieved successfully.',
        ], 200);
    }

    public function store(StoreGoodReceiptNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $grn = DB::transaction(function () use ($data) {
            $lines = $this->mapLines($data['items'] ?? []);

            $grn = GoodReceiptNote::create($this->headerAttributes($data));

            foreach ($lines as $line) {
                $grn->details()->create($this->detailAttributes($line));
            }

            return $grn->load(['details.item', 'sourceWarehouse', 'destinationWarehouse']);
        });

        return response()->json([
            'data' => $this->toResource($grn),
            'message' => 'Goods receipt note created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateGoodReceiptNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $grn = DB::transaction(function () use ($uuid, $data) {
            $grn = GoodReceiptNote::where('uuid', $uuid)->firstOrFail();

            $lines = $this->mapLines($data['items'] ?? []);

            $grn->fill($this->headerAttributes($data, $grn))->save();

            $this->syncDetails($grn, $lines);

            return $grn->fresh(['details.item', 'sourceWarehouse', 'destinationWarehouse']);
        });

        return response()->json([
            'data' => $this->toResource($grn),
            'message' => 'Goods receipt note updated successfully.',
        ], 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $uuid = (string) $request->input('id');

        DB::transaction(function () use ($uuid) {
            $grn = GoodReceiptNote::where('uuid', $uuid)->firstOrFail();

            $grn->details()->delete();
            $grn->delete();
        });

        return response()->json(['message' => 'Goods receipt note deleted successfully.'], 200);
    }

    public function bulkAction(BulkGoodReceiptNoteActionRequest $request): JsonResponse
    {
        $query = GoodReceiptNote::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(function (GoodReceiptNote $grn) {
                $grn->details()->delete();
                $grn->delete();
            }),
        };

        return response()->json(['message' => 'Bulk action completed successfully.'], 200);
    }

    protected function findByUuid(string $uuid): GoodReceiptNote
    {
        return GoodReceiptNote::with(['details.item', 'sourceWarehouse', 'destinationWarehouse'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(GoodReceiptNote $grn): array
    {
        return [
            'id' => $grn->id,
            'uuid' => $grn->uuid,
            'grnNumber' => $grn->grn_number,
            'grnDate' => $grn->grn_date?->toDateString(),
            'sourceWarehouseId' => $grn->sourceWarehouse?->uuid,
            'sourceWarehouseName' => $grn->sourceWarehouse?->name,
            'destinationWarehouseId' => $grn->destinationWarehouse?->uuid,
            'destinationWarehouseName' => $grn->destinationWarehouse?->name,
            'remark' => $grn->grn_remark,
            'status' => (bool) $grn->status,
            'items' => $grn->relationLoaded('details')
                ? $grn->details->map(fn (GoodReceiptNoteDetail $d) => $this->detailResource($d))->values()->all()
                : [],
        ];
    }

    protected function toSelectOption(GoodReceiptNote $grn): array
    {
        return [
            'value' => $grn->uuid,
            'label' => $grn->grn_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function headerAttributes(array $data, ?GoodReceiptNote $existing = null): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        $today = Carbon::today()->toDateString();
        $grnNumber = $data['grnNumber'] ?? $existing?->grn_number;
        if ($grnNumber === null || $grnNumber === '') {
            $grnNumber = $this->nextDocumentNumber(GoodReceiptNote::class, 'grn_number', 'GRN', $organisationId);
        }

        return [
            'source_warehouse_id' => $this->resolveWarehouseId($data['sourceWarehouseId'] ?? null, $organisationId)
                ?? $existing?->source_warehouse_id,
            'destination_warehouse_id' => $this->resolveWarehouseId($data['destinationWarehouseId'] ?? null, $organisationId)
                ?? $existing?->destination_warehouse_id,
            'grn_number' => $grnNumber,
            'grn_date' => $data['grnDate'] ?? $existing?->grn_date?->toDateString() ?? $today,
            'grn_remark' => $data['remark'] ?? $existing?->grn_remark,
            'status' => (bool) ($data['status'] ?? $existing?->status ?? true),
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
            $itemId = $this->resolveItemId($item['itemId'] ?? null, $organisationId);
            if (! $itemId) {
                continue;
            }

            $lines[] = [
                'uuid' => ! empty($item['uuid']) ? (string) $item['uuid'] : (string) Str::uuid(),
                'item_id' => $itemId,
                'item_uom_id' => $this->resolveItemUomId($item['uom'] ?? $item['itemUomId'] ?? null, $organisationId),
                'qty' => (float) ($item['quantity'] ?? 0),
                'reason_id' => $this->resolveReasonId($item['reason'] ?? null, $organisationId),
                'return_reason_id' => $this->resolveReasonId($item['returnReason'] ?? null, $organisationId),
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
            'qty' => $line['qty'],
            'reason_id' => $line['reason_id'],
            'return_reason_id' => $line['return_reason_id'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncDetails(GoodReceiptNote $grn, array $lines): void
    {
        $keepUuids = [];

        foreach ($lines as $line) {
            $attrs = $this->detailAttributes($line);
            $existing = $grn->details()->where('uuid', $attrs['uuid'])->first();

            if ($existing) {
                $existing->fill($attrs)->save();
            } else {
                $grn->details()->create($attrs);
            }

            $keepUuids[] = $attrs['uuid'];
        }

        $grn->details()->whereNotIn('uuid', $keepUuids)->delete();
    }

    protected function detailResource(GoodReceiptNoteDetail $detail): array
    {
        return [
            'uuid' => $detail->uuid,
            'itemId' => $detail->item?->uuid ?? $detail->item_id,
            'itemName' => $detail->item?->item_name,
            'uom' => $detail->item_uom_id,
            'quantity' => (float) $detail->qty,
            'reason' => $detail->reason?->uuid,
            'returnReason' => $detail->returnReason?->uuid,
        ];
    }
}
