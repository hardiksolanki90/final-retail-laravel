<?php

namespace App\Repositories;

use App\Models\ItemGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ItemGroupRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): ItemGroup
    {
        return ItemGroup::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): ItemGroup
    {
        return ItemGroup::create([
            'organisation_id' => $organisationId,
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): ItemGroup
    {
        $itemGroup = $this->findByUuid($uuid, $organisationId);

        $itemGroup->fill([
            'code' => $data['code'] ?? $itemGroup->code,
            'name' => $data['name'] ?? $itemGroup->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $itemGroup->status,
        ]);
        $itemGroup->save();

        return $itemGroup->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(ItemGroup $itemGroup): array
    {
        return [
            'id' => $itemGroup->id,
            'uuid' => $itemGroup->uuid,
            'code' => $itemGroup->code,
            'name' => $itemGroup->name,
            'status' => (bool) $itemGroup->status,
            'createdAt' => $itemGroup->created_at?->toISOString(),
            'updatedAt' => $itemGroup->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(ItemGroup $itemGroup): array
    {
        return [
            'id' => $itemGroup->id,
            'uuid' => $itemGroup->uuid,
            'code' => $itemGroup->code,
            'name' => $itemGroup->name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = ItemGroup::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
