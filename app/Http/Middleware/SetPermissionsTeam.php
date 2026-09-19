<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scopes every permission/role check for the rest of the request to the
 * authenticated user's own organisation (Spatie Teams). Must run after
 * auth:sanctum (needs $request->user()) and before any `permission:` gate.
 */
class SetPermissionsTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->organisation_id);
        }

        return $next($request);
    }
}
