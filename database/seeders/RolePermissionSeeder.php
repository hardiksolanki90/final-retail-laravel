<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\RoleProvisioner;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Modules that only need a `.view` permission (read-only lookups).
     */
    private const VIEW_ONLY_MODULES = ['country-master', 'currency-master'];

    /**
     * Modules that never get create/delete (an org can't create or delete
     * its own organisation row via this endpoint).
     */
    private const VIEW_EDIT_ONLY_MODULES = ['organisation'];

    /**
     * Every other real module in the app — `.view/create/edit/delete` each.
     * `credit-notes`, `users-roles` (role + invite-user) and `preferences`
     * (work-flow) already existed under the earlier 3-module pilot; this
     * reseed recreates them under the same names so nothing downstream
     * (routes, frontend) needs to change.
     */
    private const STANDARD_MODULES = [
        'area', 'bank', 'beat', 'brand', 'channel', 'consumer-survey', 'country',
        'credit-notes', 'currency', 'customer', 'customer-category', 'customer-group',
        'customer-type', 'debit-note', 'delivery', 'depot', 'division',
        'driver-replacement', 'grn', 'users-roles', 'invoice', 'item',
        'item-category', 'item-group', 'item-uom', 'journey-plan',
        'merchandiser-replacement', 'order', 'outlet-product-code', 'pallet',
        'payment-term', 'preferences', 'reason-type', 'region', 'route',
        'sales-organisation', 'salesman', 'salesman-load', 'salesman-role',
        'salesman-type', 'salesman-unload', 'sensory-survey', 'tax-rate',
        'user-credit-limit', 'van', 'van-category', 'van-type', 'warehouse', 'zone',
    ];

    public function run(): void
    {
        $this->seedPermissionCatalog();
        $this->provisionRolesForExistingOrgs();
        $this->assignExistingUsers();
    }

    private function seedPermissionCatalog(): void
    {
        $now = now();
        $rows = [];

        foreach (self::STANDARD_MODULES as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $rows[] = $this->permissionRow($module, $action, $now);
            }
        }

        foreach (self::VIEW_EDIT_ONLY_MODULES as $module) {
            foreach (['view', 'edit'] as $action) {
                $rows[] = $this->permissionRow($module, $action, $now);
            }
        }

        foreach (self::VIEW_ONLY_MODULES as $module) {
            $rows[] = $this->permissionRow($module, 'view', $now);
        }

        Permission::insert($rows);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permissionRow(string $module, string $action, $now): array
    {
        return [
            'name' => "{$module}.{$action}",
            'guard_name' => 'web',
            'module' => $module,
            'action' => $action,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function provisionRolesForExistingOrgs(): void
    {
        Organisation::pluck('id')->each(function (int $organisationId) {
            RoleProvisioner::provisionSystemRoles($organisationId);
        });
    }

    /**
     * Maps each user's pre-migration role_id (1=super-admin, 2=org-admin,
     * 3=salesman, 4=customer under the old fixed-id convention) onto their
     * own org's freshly-provisioned role copy, then assigns it for real via
     * Spatie (not the old flat FK column).
     */
    private function assignExistingUsers(): void
    {
        $codeByOldRoleId = [
            1 => 'super-admin',
            2 => 'org-admin',
            3 => 'salesman',
            4 => 'customer',
        ];

        User::whereNotNull('organisation_id')->get(['id', 'organisation_id', 'role_id'])
            ->each(function (User $user) use ($codeByOldRoleId) {
                $code = $codeByOldRoleId[$user->role_id] ?? 'org-admin';

                $role = Role::where('organisation_id', $user->organisation_id)
                    ->where('code', $code)
                    ->first();

                if (! $role) {
                    return;
                }

                app(PermissionRegistrar::class)->setPermissionsTeamId($user->organisation_id);
                $user->assignRole($role);
            });
    }
}
