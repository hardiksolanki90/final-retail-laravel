<?php

namespace App\Repositories;

use App\Models\ItemUom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ItemUomRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): ItemUom
    {
        return ItemUom::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): ItemUom
    {
        return ItemUom::create([
            'organisation_id' => $organisationId,
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): ItemUom
    {
        $itemUom = $this->findByUuid($uuid, $organisationId);

        $itemUom->fill([
            'code' => $data['code'] ?? $itemUom->code,
            'name' => $data['name'] ?? $itemUom->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $itemUom->status,
        ]);
        $itemUom->save();

        return $itemUom->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(ItemUom $itemUom): array
    {
        return [
            'id' => $itemUom->id,
            'uuid' => $itemUom->uuid,
            'code' => $itemUom->code,
            'name' => $itemUom->name,
            'status' => (bool) $itemUom->status,
            'createdAt' => $itemUom->created_at?->toISOString(),
            'updatedAt' => $itemUom->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(ItemUom $itemUom): array
    {
        return [
            'id' => $itemUom->id,
            'uuid' => $itemUom->uuid,
            'code' => $itemUom->code,
            'name' => $itemUom->name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = ItemUom::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
