<?php

namespace App\Repositories;

use App\Models\TaxRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaxRateRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): TaxRate
    {
        return TaxRate::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): TaxRate
    {
        return TaxRate::create([
            'organisation_id' => $organisationId,
            'name' => $data['name'],
            'rate' => $data['rate'],
            'type' => $data['type'],
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): TaxRate
    {
        $taxRate = $this->findByUuid($uuid, $organisationId);

        $taxRate->fill([
            'name' => $data['name'] ?? $taxRate->name,
            'rate' => $data['rate'] ?? $taxRate->rate,
            'type' => $data['type'] ?? $taxRate->type,
        ]);
        $taxRate->save();

        return $taxRate->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(TaxRate $taxRate): array
    {
        return [
            'id' => $taxRate->id,
            'uuid' => $taxRate->uuid,
            'name' => $taxRate->name,
            'rate' => $taxRate->rate,
            'type' => $taxRate->type,
            'createdAt' => $taxRate->created_at?->toISOString(),
            'updatedAt' => $taxRate->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(TaxRate $taxRate): array
    {
        return [
            'id' => $taxRate->id,
            'uuid' => $taxRate->uuid,
            'name' => $taxRate->name,
            'type' => $taxRate->type,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = TaxRate::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
