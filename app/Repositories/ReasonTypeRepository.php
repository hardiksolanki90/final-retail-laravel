<?php

namespace App\Repositories;

use App\Models\ReasonType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReasonTypeRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): ReasonType
    {
        return ReasonType::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): ReasonType
    {
        return ReasonType::create([
            'organisation_id' => $organisationId,
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? '',
            'code' => $data['code'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): ReasonType
    {
        $reasonType = $this->findByUuid($uuid, $organisationId);

        $reasonType->fill([
            'name' => $data['name'] ?? $reasonType->name,
            'type' => $data['type'] ?? $reasonType->type,
            'code' => array_key_exists('code', $data) ? $data['code'] : $reasonType->code,
            'status' => array_key_exists('status', $data) ? $data['status'] : $reasonType->status,
        ]);
        $reasonType->save();

        return $reasonType->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(ReasonType $reasonType): array
    {
        return [
            'id' => $reasonType->id,
            'uuid' => $reasonType->uuid,
            'name' => $reasonType->name,
            'type' => $reasonType->type,
            'code' => $reasonType->code,
            'status' => (bool) $reasonType->status,
            'createdAt' => $reasonType->created_at?->toISOString(),
            'updatedAt' => $reasonType->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(ReasonType $reasonType): array
    {
        return [
            'id' => $reasonType->id,
            'uuid' => $reasonType->uuid,
            'name' => $reasonType->name,
            'type' => $reasonType->type,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = ReasonType::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
