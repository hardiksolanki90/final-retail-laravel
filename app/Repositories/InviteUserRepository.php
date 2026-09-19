<?php

namespace App\Repositories;

use App\Http\Requests\StoreInviteUserRequest;
use App\Http\Requests\UpdateInviteUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Invited staff users (org-level admin/back-office accounts, usertype 4) —
 * distinct from Customer (2) and Salesman (3) accounts that also live in
 * the users table. User is excluded from the Organisationid global-scope
 * trait app-wide (login must resolve before org context exists), so every
 * query here filters by organisation_id manually instead.
 */
class InviteUserRepository
{
    public function list(Request $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;

        $query = $this->scopedQuery($organisationId);

        if ($search = $request->input('search')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        $paginated = $query
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (User $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'users'), 200);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Invited user retrieved successfully.',
        ]);
    }

    public function store(StoreInviteUserRequest $request): JsonResponse
    {
        $inviter = $request->user();
        $data = $request->validated();

        $role = Role::where('id', $data['roleId'])
            ->where('organisation_id', $inviter->organisation_id)
            ->firstOrFail();

        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'organisation_id' => $inviter->organisation_id,
            'usertype' => 4,
            'parent_id' => $inviter->parent_id,
            'country_id' => $inviter->country_id,
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'],
            // Random, never communicated — the invited user only ever sets a
            // real password through the emailed reset link below.
            'password' => Hash::make(Str::random(40)),
            'mobile' => $data['mobile'] ?? null,
            'role_id' => $role->id,
            'invited_by' => $inviter->id,
            'login_type' => 'system',
            'status' => true,
        ]);
        $user->assignRole($role);

        $this->sendInviteEmail($user);

        return response()->json([
            'data' => $this->toResource($user->load('role')),
            'message' => 'User invited successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateInviteUserRequest $request): JsonResponse
    {
        $organisationId = $request->user()->organisation_id;
        $data = $request->validated();

        $user = $this->findByUuid($uuid, $organisationId);

        $role = Role::where('id', $data['roleId'])
            ->where('organisation_id', $organisationId)
            ->firstOrFail();

        $user->fill([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'] ?? $user->lastname,
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? $user->mobile,
            'role_id' => $role->id,
            'status' => array_key_exists('status', $data) ? $data['status'] : $user->status,
        ]);
        $user->save();
        $user->syncRoles([$role]);

        return response()->json([
            'data' => $this->toResource($user->fresh('role')),
            'message' => 'Invited user updated successfully.',
        ]);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $user = $this->findByUuid($uuid, $request->user()->organisation_id);
        $user->delete();

        return response()->json(['message' => 'Invited user deleted successfully.']);
    }

    protected function findByUuid(string $uuid, int $organisationId): User
    {
        return $this->scopedQuery($organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Only usertype 4 (invited staff) accounts within the caller's own
     * organisation — never customer/salesman logins or other orgs' users.
     */
    protected function scopedQuery(int $organisationId): Builder
    {
        return User::where('organisation_id', $organisationId)
            ->where('usertype', 4)
            ->with('role');
    }

    /**
     * Reuses the same Password broker as forgot-password (AuthRepository) —
     * a signed, expiring, single-use token, not a bespoke invite mechanism.
     */
    protected function sendInviteEmail(User $user): void
    {
        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send invite email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    public function toResource(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'status' => (bool) $user->status,
            'roleId' => $user->role_id,
            'role' => $user->relationLoaded('role') && $user->role
                ? ['id' => $user->role->id, 'uuid' => $user->role->uuid, 'name' => $user->role->name]
                : null,
            'invitedBy' => $user->invited_by,
            'createdAt' => $user->created_at?->toDateTimeString(),
        ];
    }
}
