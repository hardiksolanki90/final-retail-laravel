<?php

namespace App\Repositories;

use App\Models\Van;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VanRepository
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

    public function findByUuid(string $uuid, int $organisationId): Van
    {
        return Van::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Van
    {
        return Van::create([
            'organisation_id' => $organisationId,
            'van_code' => $data['vanCode'] ?? '',
            'plate_number' => $data['plateNumber'] ?? '',
            'description' => $data['description'] ?? '',
            'capacity' => $data['capacity'] ?? null,
            'area_id' => $data['areaId'] ?? null,
            'van_type_id' => $data['vanTypeId'] ?? null,
            'van_category_id' => $data['vanCategoryId'] ?? null,
            'van_status' => $data['status'] ?? true,
            'reading' => $data['reading'] ?? 0,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Van
    {
        $van = $this->findByUuid($uuid, $organisationId);

        $van->fill([
            'van_code' => $data['vanCode'] ?? $van->van_code,
            'plate_number' => $data['plateNumber'] ?? $van->plate_number,
            'description' => $data['description'] ?? $van->description,
            'capacity' => array_key_exists('capacity', $data) ? $data['capacity'] : $van->capacity,
            'area_id' => array_key_exists('areaId', $data) ? $data['areaId'] : $van->area_id,
            'van_type_id' => $data['vanTypeId'] ?? $van->van_type_id,
            'van_category_id' => array_key_exists('vanCategoryId', $data) ? $data['vanCategoryId'] : $van->van_category_id,
            'van_status' => array_key_exists('status', $data) ? $data['status'] : $van->van_status,
            'reading' => array_key_exists('reading', $data) ? $data['reading'] : $van->reading,
        ]);
        $van->save();

        return $van->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Van $van): array
    {
        return [
            'id' => $van->id,
            'uuid' => $van->uuid,
            'vanCode' => $van->van_code,
            'plateNumber' => $van->plate_number,
            'description' => $van->description,
            'capacity' => $van->capacity,
            'areaId' => $van->area_id,
            'vanTypeId' => $van->van_type_id,
            'vanCategoryId' => $van->van_category_id,
            'status' => (bool) $van->van_status,
            'reading' => $van->reading,
            'createdAt' => $van->created_at?->toISOString(),
            'updatedAt' => $van->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Van $van): array
    {
        return [
            'id' => $van->id,
            'uuid' => $van->uuid,
            'vanCode' => $van->van_code,
            'plateNumber' => $van->plate_number,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Van::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('van_code', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (! empty($filters['van_type_id'])) {
            $query->where('van_type_id', $filters['van_type_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('van_status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
