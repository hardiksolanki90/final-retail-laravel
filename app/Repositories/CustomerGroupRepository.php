<?php

namespace App\Repositories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerGroupRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): CustomerGroup
    {
        return CustomerGroup::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): CustomerGroup
    {
        return CustomerGroup::create([
            'organisation_id' => $organisationId,
            'group_code' => $data['groupCode'] ?? $data['code'] ?? '',
            'group_name' => $data['groupName'] ?? '',
            'type' => $data['type'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): CustomerGroup
    {
        $group = $this->findByUuid($uuid, $organisationId);

        $group->fill([
            'group_code' => $data['groupCode'] ?? $data['code'] ?? $group->group_code,
            'group_name' => $data['groupName'] ?? $group->group_name,
            'type' => array_key_exists('type', $data) ? $data['type'] : $group->type,
            'status' => array_key_exists('status', $data) ? $data['status'] : $group->status,
        ]);
        $group->save();

        return $group->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(CustomerGroup $group): array
    {
        return [
            'id' => $group->id,
            'uuid' => $group->uuid,
            'groupName' => $group->group_name,
            'groupCode' => $group->group_code,
            'type' => $group->type,
            'status' => (bool) $group->status,
            'createdAt' => $group->created_at?->toISOString(),
            'updatedAt' => $group->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(CustomerGroup $group): array
    {
        return [
            'id' => $group->id,
            'uuid' => $group->uuid,
            'groupName' => $group->group_name,
            'name' => $group->group_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = CustomerGroup::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('group_code', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
