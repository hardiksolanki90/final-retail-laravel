<?php

namespace App\Repositories;

use App\Models\SalesmanInfo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesmanRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): SalesmanInfo
    {
        return SalesmanInfo::with(['user', 'supervisor'])
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): SalesmanInfo
    {
        return DB::transaction(function () use ($data, $organisationId) {
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
                'organisation_id' => $organisationId,
                'user_id' => $user->id,
                'route_id' => $data['routeId'] ?? 0,
                'salesman_type_id' => $data['salesmanTypeId'] ?? 0,
                'salesman_role_id' => $data['salesmanRoleId'] ?? 0,
                'supervisor_id' => $data['supervisorId'] ?? null,
                'designation' => $data['designation'] ?? null,
                'salesman_code' => ($data['salesmanCode'] ?? null) ?: $this->generateSalesmanCode($organisationId),
                'employee_code' => $data['employeeCode'] ?? null,
                'profile_image' => $data['profileImage'] ?? null,
                'date_of_joning' => $data['joiningDate'] ?? null,
                'status' => $data['status'] ?? true,
            ]);

            return $salesmanInfo->load(['user', 'supervisor']);
        });
    }

    public function update(string $uuid, array $data, int $organisationId): SalesmanInfo
    {
        return DB::transaction(function () use ($uuid, $data, $organisationId) {
            $salesmanInfo = SalesmanInfo::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

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

    public function delete(string $uuid, int $organisationId): void
    {
        DB::transaction(function () use ($uuid, $organisationId) {
            $salesmanInfo = SalesmanInfo::where('organisation_id', $organisationId)
                ->where('uuid', $uuid)
                ->firstOrFail();

            $salesmanInfo->user?->delete();
            $salesmanInfo->delete();
        });
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = SalesmanInfo::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'block' => $query->update(['is_block' => true]),
            'unblock' => $query->update(['is_block' => false]),
            'delete' => $query->delete(),
        };
    }

    public function sales(string $uuid, int $organisationId): array
    {
        $this->findByUuid($uuid, $organisationId);

        return [
            'salesData' => [],
            'summary' => [
                'totalOrders' => 0,
                'totalAmount' => 0,
                'customerCount' => 0,
            ],
        ];
    }

    public function loginHistory(int $userId, int $limit = 20): array
    {
        return [];
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
            'createdAt' => $salesmanInfo->created_at?->toISOString(),
            'updatedAt' => $salesmanInfo->updated_at?->toISOString(),
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

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = SalesmanInfo::with(['user', 'supervisor'])
            ->where('organisation_id', $organisationId);

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

        foreach (['route_id', 'salesman_type_id', 'salesman_role_id', 'supervisor_id'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_blocked']) && $filters['is_blocked'] !== '') {
            $query->where('is_block', filter_var($filters['is_blocked'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function generateSalesmanCode(int $organisationId): string
    {
        $count = SalesmanInfo::withTrashed()->where('organisation_id', $organisationId)->count();

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
            'createdAt' => $user->created_at?->toISOString(),
            'updatedAt' => $user->updated_at?->toISOString(),
        ];
    }
}
