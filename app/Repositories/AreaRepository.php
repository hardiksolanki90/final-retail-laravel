<?php

namespace App\Repositories;

use App\Models\Area;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AreaRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Area
    {
        return Area::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Area
    {
        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = Area::where('organisation_id', $organisationId)
                ->where('id', $parentId)
                ->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        return Area::create([
            'organisation_id' => $organisationId,
            'parent_id' => $parentId,
            'area_name' => $data['areaName'] ?? $data['name'] ?? '',
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Area
    {
        $area = $this->findByUuid($uuid, $organisationId);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $area->parent_id;
        $nodeLevel = $area->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = Area::where('organisation_id', $organisationId)
                    ->where('id', $parentId)
                    ->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $area->fill([
            'parent_id' => $parentId,
            'area_name' => $data['areaName'] ?? $data['name'] ?? $area->area_name,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => array_key_exists('status', $data) ? $data['status'] : $area->status,
        ]);
        $area->save();

        return $area->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Area $area): array
    {
        return [
            'id' => $area->id,
            'uuid' => $area->uuid,
            'areaName' => $area->area_name,
            'name' => $area->area_name,
            'parentId' => $area->parent_id,
            'nodeLevel' => $area->node_level,
            'status' => (bool) $area->status,
            'createdAt' => $area->created_at?->toISOString(),
            'updatedAt' => $area->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Area $area): array
    {
        return [
            'id' => $area->id,
            'uuid' => $area->uuid,
            'areaName' => $area->area_name,
            'name' => $area->area_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Area::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('area_name', 'like', "%{$search}%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
