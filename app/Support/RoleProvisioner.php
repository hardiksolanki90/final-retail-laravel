<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates one org's copy of the 4 system roles (Super Admin, Org Admin,
 * Salesman, Customer) — used both for brand-new organisations
 * (OrganisationRepository::save()) and for backfilling existing ones
 * (RolePermissionSeeder). Every org gets its own independently-editable
 * copy under Spatie Teams (organisation_id), rather than one shared global
 * role — see the approved plan for why.
 */
class RoleProvisioner
{
    /**
     * @return array<string, Role> keyed by role code
     */
    public static function provisionSystemRoles(int $organisationId): array
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($organisationId);

        $allPermissionNames = Permission::pluck('name')->all();

        $definitions = [
            'super-admin' => ['name' => 'Super Admin', 'permissions' => $allPermissionNames],
            'org-admin' => ['name' => 'Org Admin', 'permissions' => $allPermissionNames],
            'salesman' => ['name' => 'Salesman', 'permissions' => ['credit-notes.view']],
            'customer' => ['name' => 'Customer', 'permissions' => []],
        ];

        $roles = [];

        foreach ($definitions as $code => $definition) {
            $role = Role::create([
                'organisation_id' => $organisationId,
                'code' => $code,
                'name' => $definition['name'],
                'guard_name' => 'web',
                'status' => true,
            ]);

            if ($definition['permissions'] !== []) {
                $role->givePermissionTo(array_intersect($definition['permissions'], $allPermissionNames));
            }

            $roles[$code] = $role;
        }

        return $roles;
    }
}
