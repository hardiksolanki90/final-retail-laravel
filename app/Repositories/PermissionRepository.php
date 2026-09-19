<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionRepository
{
    public function all(): JsonResponse
    {
        $permissions = Permission::orderBy('module')->orderBy('action')->get();

        return response()->json([
            'data' => $permissions->map(fn (Permission $permission) => [
                'value' => $permission->name,
                'label' => ucfirst($permission->action).' '.str_replace('-', ' ', $permission->module),
                'module' => $permission->module,
            ])->values(),
            'message' => 'Permissions retrieved successfully.',
        ]);
    }
}
