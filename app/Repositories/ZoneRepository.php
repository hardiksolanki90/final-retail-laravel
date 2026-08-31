<?php

namespace App\Repositories;

use App\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ZoneRepository
{
    public function list(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->filtered($filters)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid): Zone
    {
        return Zone::where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Zone
    {
        return Zone::create([
            'name' => $data['name'],
            'no_truck' => $data['noTruck'],
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data): Zone
    {
        $zone = $this->findByUuid($uuid);

        $zone->fill([
            'name' => $data['name'] ?? $zone->name,
            'no_truck' => $data['noTruck'] ?? $zone->no_truck,
            'status' => array_key_exists('status', $data) ? $data['status'] : $zone->status,
        ]);
        $zone->save();

        return $zone->fresh();
    }

    public function delete(string $uuid): void
    {
        $this->findByUuid($uuid)->delete();
    }

    public function toResource(Zone $zone): array
    {
        return [
            'id' => $zone->id,
            'uuid' => $zone->uuid,
            'name' => $zone->name,
            'noTruck' => $zone->no_truck,
            'status' => (bool) $zone->status,
            'createdAt' => $zone->created_at?->toISOString(),
            'updatedAt' => $zone->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Zone $zone): array
    {
        return [
            'id' => $zone->id,
            'uuid' => $zone->uuid,
            'name' => $zone->name,
        ];
    }

    protected function filtered(array $filters): Builder
    {
        $query = Zone::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
