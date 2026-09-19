<?php

namespace App\Repositories;

use App\Http\Requests\StoreSalesmanTypeRequest;
use App\Http\Requests\UpdateSalesmanTypeRequest;
use App\Models\SalesmanType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanTypeRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = SalesmanType::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (SalesmanType $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'salesmanTypes'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Salesman type retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanTypeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = SalesmanType::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Salesman type created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->findByUuid($uuid);

        $item->fill([
            'code' => $data['code'] ?? $item->code,
            'name' => $data['name'] ?? $item->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $item->status,
        ]);
        $item->save();

        return response()->json([
            'data' => $this->toResource($item->fresh()),
            'message' => 'Salesman type updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Salesman type deleted successfully.']);
    }

    protected function findByUuid(string $uuid): SalesmanType
    {
        return SalesmanType::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(SalesmanType $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'code' => $item->code,
            'name' => $item->name,
            'status' => (bool) $item->status,
        ];
    }

    protected function toSelectOption(SalesmanType $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'code' => $item->code,
            'name' => $item->name,
        ];
    }
}
