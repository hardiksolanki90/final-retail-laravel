<?php

namespace App\Repositories;

use App\Models\OutletProductCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OutletProductCodeRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): OutletProductCode
    {
        return OutletProductCode::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): OutletProductCode
    {
        return OutletProductCode::create([
            'organisation_id' => $organisationId,
            'name' => $data['name'],
            'code' => $data['code'],
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): OutletProductCode
    {
        $outletProductCode = $this->findByUuid($uuid, $organisationId);

        $outletProductCode->fill([
            'name' => $data['name'] ?? $outletProductCode->name,
            'code' => $data['code'] ?? $outletProductCode->code,
        ]);
        $outletProductCode->save();

        return $outletProductCode->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(OutletProductCode $outletProductCode): array
    {
        return [
            'id' => $outletProductCode->id,
            'uuid' => $outletProductCode->uuid,
            'name' => $outletProductCode->name,
            'code' => $outletProductCode->code,
            'createdAt' => $outletProductCode->created_at?->toISOString(),
            'updatedAt' => $outletProductCode->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(OutletProductCode $outletProductCode): array
    {
        return [
            'id' => $outletProductCode->id,
            'uuid' => $outletProductCode->uuid,
            'name' => $outletProductCode->name,
            'code' => $outletProductCode->code,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = OutletProductCode::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
