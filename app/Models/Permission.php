<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's own Permission model — keeps every existing
 * `App\Models\Permission` import working unchanged. `module`/`action`
 * (added in the migration) ride along as plain attributes; `roles()` and
 * mass-assignment (`$guarded = []`) are inherited from Spatie.
 */
class Permission extends SpatiePermission
{
    //
}
