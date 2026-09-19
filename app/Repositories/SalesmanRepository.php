<?php

namespace App\Repositories;

use App\Http\Requests\BulkSalesmanActionRequest;
use App\Http\Requests\StoreSalesmanRequest;
use App\Http\Requests\UpdateSalesmanRequest;
use App\Models\SalesmanInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesmanRepository
{
    public function list(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'route_id', 'salesman_type_id', 'salesman_role_id', 'supervisor_id', 'status', 'is_blocked']);

        $paginated = $this->baseQuery($filters)
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (SalesmanInfo $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'salesmen'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'route_id', 'status']);

        $paginated = $this->baseQuery($filters)
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (SalesmanInfo $salesman) => $this->toSelectOption($salesman));

        return response()->json(paginated($paginated, 'salesmen'), 200);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $salesman = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($salesman),
            'message' => 'Salesman retrieved successfully.',
        ]);
    }

    public function store(StoreSalesmanRequest $request): JsonResponse
    {
        $salesman = $this->create($request->validated());

        return response()->json([
            'data' => $this->toResource($salesman),
            'message' => 'Salesman created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesmanRequest $request): JsonResponse
    {
        $salesman = $this->performUpdate($uuid, $request->validated());

        return response()->json([
            'data' => $this->toResource($salesman),
            'message' => 'Salesman updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        DB::transaction(function () use ($request) {
            $salesmanInfo = SalesmanInfo::where('uuid', (string) $request->input('id'))->firstOrFail();

            $salesmanInfo->user?->delete();
            $salesmanInfo->delete();
        });

        return response()->json(['message' => 'Salesman deleted successfully.']);
    }

    public function bulkAction(BulkSalesmanActionRequest $request): JsonResponse
    {
        $query = SalesmanInfo::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'block' => $query->update(['is_block' => true]),
            'unblock' => $query->update(['is_block' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    public function sales(string $uuid, Request $request): JsonResponse
    {
        $this->findByUuid($uuid);

        return response()->json([
            'data' => [
                'salesData' => [],
                'summary' => [
                    'totalOrders' => 0,
                    'totalAmount' => 0,
                    'customerCount' => 0,
                ],
            ],
            'message' => 'Salesman sales retrieved successfully.',
        ]);
    }

    public function loginHistory(int $userId, Request $request): JsonResponse
    {
        return response()->json([
            'data' => [],
            'message' => 'Salesman login history retrieved successfully.',
        ]);
    }

    protected function findByUuid(string $uuid): SalesmanInfo
    {
        return SalesmanInfo::with(['user', 'supervisor'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Creates the salesman's user login inside the same DB transaction as
     * the SalesmanInfo row — the two are inseparable, a salesman always has
     * exactly one backing User account. Do not split this transaction.
     */
    protected function create(array $data): SalesmanInfo
    {
        return DB::transaction(function () use ($data) {
            // User is intentionally excluded from automatic organisation
            // scoping, so organisation_id must still be set explicitly here.
            $organisationId = Auth::user()->organisation_id;

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'organisation_id' => $organisationId,
                'usertype' => 3,
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'] ?? '',
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => Str::random(60),
                'mobile' => $data['mobile'] ?? null,
                'country_id' => $data['countryId'] ?? null,
                'login_type' => 'system',
                'status' => $data['status'] ?? true,
            ]);

            $salesmanInfo = SalesmanInfo::create([
                'user_id' => $user->id,
                'route_id' => $data['routeId'] ?? 0,
                'salesman_type_id' => $data['salesmanTypeId'] ?? 0,
                'salesman_role_id' => $data['salesmanRoleId'] ?? 0,
                'supervisor_id' => $data['supervisorId'] ?? null,
                'designation' => $data['designation'] ?? null,
                'salesman_code' => ($data['salesmanCode'] ?? null) ?: $this->generateSalesmanCode(),
                'employee_code' => $data['employeeCode'] ?? null,
                'profile_image' => $data['profileImage'] ?? null,
                'date_of_joning' => $data['joiningDate'] ?? null,
                'status' => $data['status'] ?? true,
            ]);

            return $salesmanInfo->load(['user', 'supervisor']);
        });
    }

    /**
     * Updates the salesman's linked user row inside the same DB transaction
     * as the SalesmanInfo row — see create() above. Do not split this
     * transaction.
     */
    protected function performUpdate(string $uuid, array $data): SalesmanInfo
    {
        return DB::transaction(function () use ($uuid, $data) {
            $salesmanInfo = SalesmanInfo::where('uuid', $uuid)->firstOrFail();

            $user = $salesmanInfo->user;

            if ($user && $data['email'] !== $user->email) {
                $emailTaken = User::where('email', $data['email'])
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($emailTaken) {
                    throw ValidationException::withMessages([
                        'email' => ['The email has already been taken.'],
                    ]);
                }
            }

            $user?->fill([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'] ?? '',
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                'country_id' => $data['countryId'] ?? null,
                'status' => $data['status'] ?? $user->status,
            ]);

            if (! empty($data['password'])) {
                $user?->forceFill(['password' => Hash::make($data['password'])]);
            }

            $user?->save();

            $salesmanInfo->fill([
                'route_id' => $data['routeId'] ?? $salesmanInfo->route_id,
                'salesman_type_id' => $data['salesmanTypeId'] ?? $salesmanInfo->salesman_type_id,
                'salesman_role_id' => $data['salesmanRoleId'] ?? $salesmanInfo->salesman_role_id,
                'supervisor_id' => $data['supervisorId'] ?? null,
                'designation' => $data['designation'] ?? null,
                'salesman_code' => ($data['salesmanCode'] ?? null) ?: $salesmanInfo->salesman_code,
                'employee_code' => $data['employeeCode'] ?? null,
                'profile_image' => $data['profileImage'] ?? null,
                'date_of_joning' => $data['joiningDate'] ?? null,
                'status' => $data['status'] ?? $salesmanInfo->status,
            ]);
            $salesmanInfo->save();

            return $salesmanInfo->fresh(['user', 'supervisor']);
        });
    }

    public function toResource(SalesmanInfo $salesmanInfo): array
    {
        return [
            'id' => $salesmanInfo->id,
            'uuid' => $salesmanInfo->uuid,
            'userId' => $salesmanInfo->user_id,
            'employeeCode' => $salesmanInfo->employee_code,
            'salesmanCode' => $salesmanInfo->salesman_code,
            'designation' => $salesmanInfo->designation,
            'joiningDate' => $salesmanInfo->date_of_joning?->toDateString(),
            'profileImage' => $salesmanInfo->profile_image,
            'status' => (bool) $salesmanInfo->status,
            'isBlocked' => (bool) $salesmanInfo->is_block,
            'blockStartDate' => $salesmanInfo->block_start_date?->toDateString(),
            'blockEndDate' => $salesmanInfo->block_end_date?->toDateString(),
            'canTakeOrders' => (bool) $salesmanInfo->status && ! $salesmanInfo->is_block,
            'user' => $salesmanInfo->relationLoaded('user') && $salesmanInfo->user
                ? $this->userResource($salesmanInfo->user)
                : null,
            'supervisor' => $salesmanInfo->relationLoaded('supervisor') && $salesmanInfo->supervisor
                ? ['id' => $salesmanInfo->supervisor->id, 'name' => $this->fullName($salesmanInfo->supervisor)]
                : null,
        ];
    }

    public function toSelectOption(SalesmanInfo $salesmanInfo): array
    {
        return [
            'id' => $salesmanInfo->id,
            'uuid' => $salesmanInfo->uuid,
            'userId' => $salesmanInfo->user_id,
            'salesmanCode' => $salesmanInfo->salesman_code,
            'name' => $salesmanInfo->user ? $this->fullName($salesmanInfo->user) : '',
        ];
    }

    /**
     * Builds the SalesmanInfo query for list()/all(): Filterable::scopeFilter
     * handles the plain-column filters (route_id, salesman_type_id,
     * salesman_role_id, supervisor_id, status) declared in the model's
     * $filterable, while the relation-spanning search and the is_block
     * (renamed from filter key is_blocked) boolean are applied manually here.
     */
    protected function baseQuery(array $filters): Builder
    {
        $query = SalesmanInfo::with(['user', 'supervisor'])
            ->filter(collect($filters)->except(['search', 'is_blocked'])->all());

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('salesman_code', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['is_blocked']) && $filters['is_blocked'] !== '') {
            $query->where('is_block', filter_var($filters['is_blocked'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function generateSalesmanCode(): string
    {
        $count = SalesmanInfo::withTrashed()->count();

        return 'SM-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    protected function fullName(User $user): string
    {
        return trim($user->firstname.' '.$user->lastname);
    }

    protected function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'usertype' => $user->usertype,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullName' => $this->fullName($user),
            'email' => $user->email,
            'mobile' => $user->mobile,
            'countryId' => $user->country_id,
            'isApprovedByAdmin' => (bool) $user->is_approved_by_admin,
            'status' => (bool) $user->status,
            'loginType' => $user->login_type,
        ];
    }
}
