<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [];
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            $permissions[] = [
                'name' => "preferences.{$action}",
                'module' => 'preferences',
                'action' => $action,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('permissions')->insert($permissions);

        $permissionIdsByName = DB::table('permissions')->where('module', 'preferences')->pluck('id', 'name');

        // Super Admin (1) and Org Admin (2) get full access; Salesman (3) and
        // Customer (4) get none — matches the seeding migration this one follows.
        $rolePermissionRows = [];
        foreach ([1, 2] as $roleId) {
            foreach ($permissionIdsByName as $permissionId) {
                $rolePermissionRows[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permissions')->insert($rolePermissionRows);
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->where('module', 'preferences')->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->where('module', 'preferences')->delete();
    }
};
