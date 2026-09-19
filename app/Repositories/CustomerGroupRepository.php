<?php

namespace App\Repositories;

use App\Http\Requests\StoreCustomerGroupRequest;
use App\Http\Requests\UpdateCustomerGroupRequest;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerGroupRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = CustomerGroup::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (CustomerGroup $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'customerGroups'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer group retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = CustomerGroup::create([
            'group_code' => $data['groupCode'] ?? $data['code'] ?? '',
            'group_name' => $data['groupName'] ?? '',
            'type' => $data['type'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Customer group created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerGroupRequest $request): JsonResponse
    {
        $data = $request->validated();
        $group = $this->findByUuid($uuid);

        $group->fill([
            'group_code' => $data['groupCode'] ?? $data['code'] ?? $group->group_code,
            'group_name' => $data['groupName'] ?? $group->group_name,
            'type' => array_key_exists('type', $data) ? $data['type'] : $group->type,
            'status' => array_key_exists('status', $data) ? $data['status'] : $group->status,
        ]);
        $group->save();

        return response()->json([
            'data' => $this->toResource($group->fresh()),
            'message' => 'Customer group updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Customer group deleted successfully.']);
    }

    protected function findByUuid(string $uuid): CustomerGroup
    {
        return CustomerGroup::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(CustomerGroup $group): array
    {
        return [
            'id' => $group->id,
            'uuid' => $group->uuid,
            'groupName' => $group->group_name,
            'groupCode' => $group->group_code,
            'type' => $group->type,
            'status' => (bool) $group->status,
        ];
    }

    protected function toSelectOption(CustomerGroup $group): array
    {
        return [
            'id' => $group->id,
            'uuid' => $group->uuid,
            'groupName' => $group->group_name,
            'name' => $group->group_name,
        ];
    }
}
