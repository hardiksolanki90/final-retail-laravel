<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $hasPermission = $user->role()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();

        if (! $hasPermission) {
            abort(403, "Missing required permission: {$permission}");
        }

        return $next($request);
    }
}
