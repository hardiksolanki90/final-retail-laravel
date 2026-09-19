<?php

namespace App\Repositories;

use App\Http\Requests\StoreOutletProductCodeRequest;
use App\Http\Requests\UpdateOutletProductCodeRequest;
use App\Models\OutletProductCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletProductCodeRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = OutletProductCode::filter($request->only(['search']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (OutletProductCode $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'outletProductCodes'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = OutletProductCode::filter($request->only(['search']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (OutletProductCode $item) => $this->toSelectOption($item))->values(),
            'message' => 'Outlet product codes retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Outlet product code retrieved successfully.',
        ]);
    }

    public function store(StoreOutletProductCodeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = OutletProductCode::create([
            'name' => $data['name'],
            'code' => $data['code'],
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Outlet product code created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateOutletProductCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $outletProductCode = $this->findByUuid($uuid);

        $outletProductCode->fill([
            'name' => $data['name'] ?? $outletProductCode->name,
            'code' => $data['code'] ?? $outletProductCode->code,
        ]);
        $outletProductCode->save();

        return response()->json([
            'data' => $this->toResource($outletProductCode->fresh()),
            'message' => 'Outlet product code updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Outlet product code deleted successfully.']);
    }

    protected function findByUuid(string $uuid): OutletProductCode
    {
        return OutletProductCode::where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(OutletProductCode $outletProductCode): array
    {
        return [
            'id' => $outletProductCode->id,
            'uuid' => $outletProductCode->uuid,
            'name' => $outletProductCode->name,
            'code' => $outletProductCode->code,
        ];
    }

    protected function toSelectOption(OutletProductCode $outletProductCode): array
    {
        return [
            'id' => $outletProductCode->id,
            'uuid' => $outletProductCode->uuid,
            'name' => $outletProductCode->name,
            'code' => $outletProductCode->code,
        ];
    }
}
