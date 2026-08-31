<?php

namespace App\Repositories;

use App\Models\UserCreditLimit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserCreditLimitRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->with('user')->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): UserCreditLimit
    {
        return UserCreditLimit::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): UserCreditLimit
    {
        return UserCreditLimit::create([
            'organisation_id' => $organisationId,
            'user_id' => $data['userId'],
            'credit_limit_type' => $data['creditLimitType'],
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): UserCreditLimit
    {
        $userCreditLimit = $this->findByUuid($uuid, $organisationId);

        $userCreditLimit->fill([
            'user_id' => $data['userId'] ?? $userCreditLimit->user_id,
            'credit_limit_type' => $data['creditLimitType'] ?? $userCreditLimit->credit_limit_type,
        ]);
        $userCreditLimit->save();

        return $userCreditLimit->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(UserCreditLimit $userCreditLimit): array
    {
        $resource = [
            'id' => $userCreditLimit->id,
            'uuid' => $userCreditLimit->uuid,
            'userId' => $userCreditLimit->user_id,
            'creditLimitType' => $userCreditLimit->credit_limit_type,
            'createdAt' => $userCreditLimit->created_at?->toISOString(),
            'updatedAt' => $userCreditLimit->updated_at?->toISOString(),
        ];

        if ($userCreditLimit->relationLoaded('user') && $userCreditLimit->user) {
            $resource['user'] = [
                'id' => $userCreditLimit->user->id,
                'name' => trim($userCreditLimit->user->firstname.' '.$userCreditLimit->user->lastname),
            ];
        }

        return $resource;
    }

    public function toSelectOption(UserCreditLimit $userCreditLimit): array
    {
        return [
            'id' => $userCreditLimit->id,
            'uuid' => $userCreditLimit->uuid,
            'userId' => $userCreditLimit->user_id,
            'creditLimitType' => $userCreditLimit->credit_limit_type,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = UserCreditLimit::where('organisation_id', $organisationId);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['credit_limit_type'])) {
            $query->where('credit_limit_type', $filters['credit_limit_type']);
        }

        return $query;
    }
}
