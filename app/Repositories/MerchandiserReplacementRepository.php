<?php

namespace App\Repositories;

use App\Models\MerchandiserReplacement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MerchandiserReplacementRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->with(['oldSalesman', 'newSalesman'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)
            ->with(['oldSalesman', 'newSalesman'])
            ->orderBy('id')
            ->get();
    }

    public function findByUuid(string $uuid, int $organisationId): MerchandiserReplacement
    {
        return MerchandiserReplacement::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): MerchandiserReplacement
    {
        return MerchandiserReplacement::create([
            'organisation_id' => $organisationId,
            'old_salesman_id' => $data['oldSalesmanId'],
            'new_salesman_id' => $data['newSalesmanId'],
            'type' => $data['type'],
            'added_on' => $data['addedOn'],
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): MerchandiserReplacement
    {
        $item = $this->findByUuid($uuid, $organisationId);

        $item->fill([
            'old_salesman_id' => $data['oldSalesmanId'] ?? $item->old_salesman_id,
            'new_salesman_id' => $data['newSalesmanId'] ?? $item->new_salesman_id,
            'type' => $data['type'] ?? $item->type,
            'added_on' => $data['addedOn'] ?? $item->added_on,
        ]);
        $item->save();

        return $item->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(MerchandiserReplacement $item): array
    {
        $resource = [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'oldSalesmanId' => $item->old_salesman_id,
            'newSalesmanId' => $item->new_salesman_id,
            'type' => $item->type,
            'addedOn' => $item->added_on?->toDateString(),
            'createdAt' => $item->created_at?->toISOString(),
            'updatedAt' => $item->updated_at?->toISOString(),
        ];

        if ($item->relationLoaded('oldSalesman') && $item->oldSalesman) {
            $resource['oldSalesman'] = [
                'id' => $item->oldSalesman->id,
                'name' => trim($item->oldSalesman->firstname.' '.$item->oldSalesman->lastname),
            ];
        }

        if ($item->relationLoaded('newSalesman') && $item->newSalesman) {
            $resource['newSalesman'] = [
                'id' => $item->newSalesman->id,
                'name' => trim($item->newSalesman->firstname.' '.$item->newSalesman->lastname),
            ];
        }

        return $resource;
    }

    public function toSelectOption(MerchandiserReplacement $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'type' => $item->type,
            'addedOn' => $item->added_on?->toDateString(),
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = MerchandiserReplacement::where('organisation_id', $organisationId);

        if (! empty($filters['old_salesman_id'])) {
            $query->where('old_salesman_id', $filters['old_salesman_id']);
        }

        if (! empty($filters['new_salesman_id'])) {
            $query->where('new_salesman_id', $filters['new_salesman_id']);
        }

        return $query;
    }
}
