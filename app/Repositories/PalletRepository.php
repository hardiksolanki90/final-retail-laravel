<?php

namespace App\Repositories;

use App\Http\Requests\BulkPalletActionRequest;
use App\Http\Requests\StorePalletRequest;
use App\Models\Pallet;
use App\Models\SalesmanInfo;
use App\Repositories\Concerns\ResolvesDocumentRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PalletRepository
{
    use ResolvesDocumentRelations;

    /**
     * Summary of allocated/return pallet quantities grouped by salesman —
     * matches PalletList.tsx's aggregated table shape, not a raw row dump.
     */
    public function list(Request $request): JsonResponse
    {
        $paginated = Pallet::query()
            ->selectRaw('salesman_id')
            ->selectRaw("SUM(CASE WHEN pallet_type = 'allocated' THEN qty ELSE 0 END) as total_allocated")
            ->selectRaw("SUM(CASE WHEN pallet_type = 'return' THEN qty ELSE 0 END) as total_return")
            ->groupBy('salesman_id')
            ->filter($request->only(['date_from', 'date_to']))
            ->paginate((int) $request->input('per_page', 15));

        $salesmanIds = collect($paginated->items())->pluck('salesman_id')->all();
        $salesmanInfos = SalesmanInfo::with('user')
            ->whereIn('user_id', $salesmanIds)
            ->get()
            ->keyBy('user_id');

        $paginated->getCollection()->transform(function ($row) use ($salesmanInfos) {
            $info = $salesmanInfos->get($row->salesman_id);
            $allocated = (float) $row->total_allocated;
            $return = (float) $row->total_return;

            return [
                'salesmanCode' => $info?->salesman_code,
                'salesman' => $info?->user ? trim($info->user->firstname.' '.$info->user->lastname) : null,
                'totalPalletAllocated' => $allocated,
                'totalReturn' => $return,
                'pending' => $allocated - $return,
            ];
        });

        return response()->json(paginated($paginated, 'pallets'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $pallet = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($pallet),
            'message' => 'Pallet retrieved successfully.',
        ]);
    }

    public function store(StorePalletRequest $request): JsonResponse
    {
        $pallet = Pallet::create($this->attributes($request->validated()));
        $pallet->load(['salesman', 'item', 'division', 'warehouse']);

        return response()->json([
            'data' => $this->toResource($pallet),
            'message' => 'Pallet created successfully.',
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        Pallet::where('uuid', (string) $request->input('id'))->firstOrFail()->delete();

        return response()->json(['message' => 'Pallet deleted successfully.']);
    }

    public function bulkAction(BulkPalletActionRequest $request): JsonResponse
    {
        $query = Pallet::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): Pallet
    {
        return Pallet::with(['salesman', 'item', 'division', 'warehouse'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(Pallet $pallet): array
    {
        return [
            'id' => $pallet->id,
            'uuid' => $pallet->uuid,
            'date' => $pallet->date?->toDateString(),
            'salesmanId' => (string) $pallet->salesman_id,
            'itemId' => $pallet->item?->uuid,
            'divisionId' => $pallet->division?->uuid,
            'divisionName' => $pallet->division?->name,
            'warehouseId' => $pallet->warehouse?->uuid,
            'qty' => (float) $pallet->qty,
            'palletType' => $pallet->pallet_type,
            'status' => (bool) $pallet->status,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributes(array $data): array
    {
        $organisationId = (int) Auth::user()->organisation_id;

        return [
            'date' => $data['date'],
            'salesman_id' => $this->resolveSalesmanId($data['salesmanId'] ?? null, $organisationId),
            'item_id' => $this->resolveItemId($data['itemId'] ?? null, $organisationId),
            'division_id' => $this->resolveDivisionId($data['divisionId'] ?? null, $organisationId),
            'warehouse_id' => $this->resolveWarehouseId($data['warehouseId'] ?? null, $organisationId),
            'qty' => $data['qty'] ?? 0,
            'pallet_type' => $data['palletType'] ?? 'allocated',
            'status' => true,
        ];
    }
}
