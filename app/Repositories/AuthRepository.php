<?php

namespace App\Repositories;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class AuthRepository
{
    public function __construct(protected OrganisationRepository $organisations) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Registration only creates the user account. No organisation row
        // exists yet — the user creates one by completing the Organisation
        // Details screen, which is gated until they do (OrganisationGuard).
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'organisation_id' => null,
            'usertype' => 1,
            'parent_id' => null,
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'] ?? '',
            'email' => $validated['email'],
            'password' => $validated['password'],
            'api_token' => Str::random(60),
            'is_approved_by_admin' => true,
            'status' => true,
            'login_type' => 'system',
            'role_id' => 2,
        ]);

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'usertype' => $user->usertype,
                    'role_id' => $user->role_id,
                    'role' => $this->roleResource($user),
                    'organisation_id' => $user->organisation_id,
                    'organisation' => null,
                ],
                'token' => $token,
            ],
            'message' => 'Registration successful.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var User $user */
        $user = Auth::user();
        $user->load('organisation');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'usertype' => $user->usertype,
                    'role_id' => $user->role_id,
                    'role' => $this->roleResource($user),
                    'organisation_id' => $user->organisation_id,
                    'organisation' => $user->organisation ? $this->organisations->toResource($user->organisation) : null,
                ],
                'token' => $token,
            ],
            'message' => 'Login successful.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('organisation');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'usertype' => $user->usertype,
                'role_id' => $user->role_id,
                'role' => $this->roleResource($user),
                'organisation_id' => $user->organisation_id,
                'organisation' => $user->organisation ? $this->organisations->toResource($user->organisation) : null,
            ],
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['message' => __($status)]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => ['required', 'same:password'],
        ]);

        // Only email/password/token — the broker's user lookup excludes just
        // those three keys, so a stray camelCase key here would otherwise
        // leak into the lookup query as a bogus WHERE clause.
        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->password = $password; // hashed via the 'password' => 'hashed' cast
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['message' => __($status)]);
    }

    public function logout(Request $request): JsonResponse
    {
        // For session/cookie-authenticated requests, currentAccessToken() returns
        // a TransientToken stand-in (no DB row, no delete() method) rather than a
        // real PersonalAccessToken — only delete it when it's an actual token.
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @return array{id: int, name: string, permissions: string[]}|null
     */
    protected function roleResource(User $user): ?array
    {
        if (! $user->organisation_id) {
            return null;
        }

        // Spatie Teams scopes every role/permission lookup to the "current
        // team" set on the registrar — for routes outside the auth:sanctum
        // group (login/register have no request-scoped team context yet)
        // this must be set explicitly rather than relying on
        // SetPermissionsTeam having already run.
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->organisation_id);

        $role = $user->roles()->first();

        if (! $role) {
            return null;
        }

        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }
}
