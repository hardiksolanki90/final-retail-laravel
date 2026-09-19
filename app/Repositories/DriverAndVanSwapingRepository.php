<?php

namespace App\Repositories;

use App\Http\Requests\StoreDriverAndVanSwapingRequest;
use App\Http\Requests\UpdateDriverAndVanSwapingRequest;
use App\Models\DriverAndVanSwaping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverAndVanSwapingRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = DriverAndVanSwaping::filter($request->only(['old_salesman_id', 'new_salesman_id', 'reason_id']))
            ->with(['newSalesman', 'oldSalesman', 'oldVan', 'newVan', 'reason'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (DriverAndVanSwaping $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'driverReplacements'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = DriverAndVanSwaping::filter($request->only(['old_salesman_id', 'new_salesman_id', 'reason_id']))
            ->with(['newSalesman', 'oldSalesman', 'oldVan', 'newVan', 'reason'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (DriverAndVanSwaping $item) => $this->toSelectOption($item))->values(),
            'message' => 'Driver and van swapings retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Driver and van swaping retrieved successfully.',
        ]);
    }

    public function store(StoreDriverAndVanSwapingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = DriverAndVanSwaping::create([
            'order_id' => $data['orderId'] ?? null,
            'new_salesman_id' => $data['newSalesmanId'] ?? null,
            'old_salesman_id' => $data['oldSalesmanId'] ?? null,
            'old_van_id' => $data['oldVanId'] ?? null,
            'new_van_id' => $data['newVanId'] ?? null,
            'login_user_id' => $request->user()->id,
            'reason_id' => $data['reasonId'] ?? null,
            'date' => $data['date'],
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Driver and van swaping created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDriverAndVanSwapingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->findByUuid($uuid);

        $item->fill([
            'order_id' => array_key_exists('orderId', $data) ? $data['orderId'] : $item->order_id,
            'new_salesman_id' => array_key_exists('newSalesmanId', $data) ? $data['newSalesmanId'] : $item->new_salesman_id,
            'old_salesman_id' => array_key_exists('oldSalesmanId', $data) ? $data['oldSalesmanId'] : $item->old_salesman_id,
            'old_van_id' => array_key_exists('oldVanId', $data) ? $data['oldVanId'] : $item->old_van_id,
            'new_van_id' => array_key_exists('newVanId', $data) ? $data['newVanId'] : $item->new_van_id,
            'reason_id' => array_key_exists('reasonId', $data) ? $data['reasonId'] : $item->reason_id,
            'date' => $data['date'] ?? $item->date,
        ]);
        $item->save();

        return response()->json([
            'data' => $this->toResource($item->fresh()),
            'message' => 'Driver and van swaping updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Driver and van swaping deleted successfully.']);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Driver and van swaping deleted successfully.']);
    }

    protected function findByUuid(string $uuid): DriverAndVanSwaping
    {
        return DriverAndVanSwaping::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(DriverAndVanSwaping $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'orderId' => $item->order_id,
            'newSalesmanId' => $item->new_salesman_id,
            'oldSalesmanId' => $item->old_salesman_id,
            'oldVanId' => $item->old_van_id,
            'newVanId' => $item->new_van_id,
            'loginUserId' => $item->login_user_id,
            'reasonId' => $item->reason_id,
            'date' => $item->date?->toDateString(),
        ];
    }

    protected function toSelectOption(DriverAndVanSwaping $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'date' => $item->date?->toDateString(),
        ];
    }
}
