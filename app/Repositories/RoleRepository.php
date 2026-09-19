<?php

namespace App\Repositories;

use App\Http\Requests\BulkRoleActionRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class RoleRepository
{
    public function list(Request $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;

        $paginated = $this->scopedQuery($organisationId)
            ->filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Role $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'roles'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;

        $items = $this->scopedQuery($organisationId)
            ->filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Role $item) => $this->toSelectOption($item))->values(),
            'message' => 'Roles retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Role retrieved successfully.',
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;
        $data = $request->validated();

        app(PermissionRegistrar::class)->setPermissionsTeamId($organisationId);

        $role = Role::create([
            'organisation_id' => $organisationId,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? '',
            'guard_name' => 'web',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json([
            'data' => $this->toResource($role->load('permissions')),
            'message' => 'Role created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateRoleRequest $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;
        $data = $request->validated();

        $role = $this->findByUuid($uuid, $organisationId);
        $this->assertMutable($role);

        $role->fill([
            'code' => array_key_exists('code', $data) ? $data['code'] : $role->code,
            'name' => $data['name'] ?? $role->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $role->description,
            'status' => array_key_exists('status', $data) ? $data['status'] : $role->status,
        ]);
        $role->save();

        if (array_key_exists('permissions', $data)) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($organisationId);
            $role->syncPermissions($data['permissions']);
        }

        return response()->json([
            'data' => $this->toResource($role->fresh('permissions')),
            'message' => 'Role updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);
        $organisationId = $request->user()->organisation_id;

        $role = $this->findByUuid((string) $request->input('id'), $organisationId);
        $this->assertMutable($role);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function bulkAction(BulkRoleActionRequest $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;

        $query = Role::where('organisation_id', $organisationId)->whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->get()->each(fn (Role $role) => $role->delete()),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid, int $organisationId): Role
    {
        return $this->scopedQuery($organisationId)
            ->with('permissions')
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * The 4 system roles (one copy per org, provisioned by RoleProvisioner)
     * are protected from edit/delete — losing "Org Admin" would lock an org
     * out of managing itself.
     */
    protected function assertMutable(Role $role): void
    {
        if (in_array($role->code, ['super-admin', 'org-admin', 'salesman', 'customer'], true)) {
            abort(403, 'System roles cannot be modified or deleted.');
        }
    }

    public function toResource(Role $role): array
    {
        return [
            'id' => $role->id,
            'uuid' => $role->uuid,
            'code' => $role->code,
            'name' => $role->name,
            'description' => $role->description,
            'status' => (bool) $role->status,
            'permissions' => $role->relationLoaded('permissions')
                ? $role->permissions->pluck('name')->values()->all()
                : [],
        ];
    }

    public function toSelectOption(Role $role): array
    {
        return [
            'value' => $role->uuid,
            'label' => $role->name,
        ];
    }

    /**
     * Every org gets its own copy of every role (including the 4 system
     * roles) under Spatie Teams — no more null-organisation "shared" roles,
     * so this is a plain, single-tenant filter now.
     */
    protected function scopedQuery(int $organisationId): Builder
    {
        return Role::where('organisation_id', $organisationId)->with('permissions');
    }
}
