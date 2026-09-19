<?php

namespace App\Repositories;

use App\Http\Requests\StoreSalesmanRoleRequest;
use App\Http\Requests\UpdateSalesmanRoleRequest;
use App\Models\SalesmanRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanRoleRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = SalesmanRole::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (SalesmanRole $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'salesmanRoles'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Salesman role retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanRoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = SalesmanRole::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Salesman role created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanRoleRequest $request): JsonResponse
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
            'message' => 'Salesman role updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Salesman role deleted successfully.']);
    }

    protected function findByUuid(string $uuid): SalesmanRole
    {
        return SalesmanRole::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(SalesmanRole $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'code' => $item->code,
            'name' => $item->name,
            'status' => (bool) $item->status,
        ];
    }

    protected function toSelectOption(SalesmanRole $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'code' => $item->code,
            'name' => $item->name,
        ];
    }
}
