<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Seeds the 4 system roles at fixed IDs matching the pre-existing
     * hardcoded users.role_id convention (see 0001_01_01_000010_create_users_table.php's
     * column comment: "1:superadmin, 2 org-admin, 3...") and a permission
     * catalog scoped to the RBAC pilot (CreditNotes + the Users & Roles admin
     * page itself). More permissions get added the same way when enforcement
     * extends past the pilot.
     */
    public function up(): void
    {
        $now = now();

        $roles = [
            ['id' => 1, 'code' => 'super-admin', 'name' => 'Super Admin'],
            ['id' => 2, 'code' => 'org-admin', 'name' => 'Org Admin'],
            ['id' => 3, 'code' => 'salesman', 'name' => 'Salesman'],
            ['id' => 4, 'code' => 'customer', 'name' => 'Customer'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'id' => $role['id'],
                'uuid' => (string) Str::uuid(),
                'organisation_id' => null,
                'code' => $role['code'],
                'name' => $role['name'],
                'description' => null,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [];
        foreach (['credit-notes', 'users-roles'] as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissions[] = [
                    'name' => "{$module}.{$action}",
                    'module' => $module,
                    'action' => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('permissions')->insert($permissions);

        $permissionIdsByName = DB::table('permissions')->pluck('id', 'name');

        $assignments = [
            1 => array_keys($permissionIdsByName->all()), // super-admin: everything
            2 => array_keys($permissionIdsByName->all()), // org-admin: everything
            3 => ['credit-notes.view'],                    // salesman: read-only
            4 => [],                                        // customer: none
        ];

        $rolePermissionRows = [];
        foreach ($assignments as $roleId => $permissionNames) {
            foreach ($permissionNames as $permissionName) {
                $rolePermissionRows[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionIdsByName[$permissionName],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rolePermissionRows !== []) {
            DB::table('role_permissions')->insert($rolePermissionRows);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->whereIn('role_id', [1, 2, 3, 4])->delete();
        DB::table('permissions')->whereIn('module', ['credit-notes', 'users-roles'])->delete();
        DB::table('roles')->whereIn('id', [1, 2, 3, 4])->delete();
    }
};
