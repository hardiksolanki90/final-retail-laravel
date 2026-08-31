<?php

namespace App\Repositories;

use App\Models\DriverAndVanSwaping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DriverAndVanSwapingRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->with(['newSalesman', 'oldSalesman', 'oldVan', 'newVan', 'reason'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)
            ->with(['newSalesman', 'oldSalesman', 'oldVan', 'newVan', 'reason'])
            ->orderBy('id')
            ->get();
    }

    public function findByUuid(string $uuid, int $organisationId): DriverAndVanSwaping
    {
        return DriverAndVanSwaping::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId, int $loginUserId): DriverAndVanSwaping
    {
        return DriverAndVanSwaping::create([
            'organisation_id' => $organisationId,
            'order_id' => $data['orderId'] ?? null,
            'new_salesman_id' => $data['newSalesmanId'] ?? null,
            'old_salesman_id' => $data['oldSalesmanId'] ?? null,
            'old_van_id' => $data['oldVanId'] ?? null,
            'new_van_id' => $data['newVanId'] ?? null,
            'login_user_id' => $loginUserId,
            'reason_id' => $data['reasonId'] ?? null,
            'date' => $data['date'],
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): DriverAndVanSwaping
    {
        $item = $this->findByUuid($uuid, $organisationId);

        $item->fill([
            'order_id' => array_key_exists('orderId', $data) ? $data['orderId'] : $item->order_id,
            'new_salesman_id' => array_key_exists('newSalesmanId', $data) ? $data['newSalesmanId'] : $item->new_salesman_id,
            'old_salesman_id' => array_key_exists('oldSalesmanId', $data) ? $data['oldSalesmanId'] : $item->old_salesman_id,
            'old_van_id' => array_key_exists('oldVanId', $data) ? $data['oldVanId'] : $item->old_van_id,
            'new_van_id' => array_key_exists('newVanId', $data) ? $data['newVanId'] : $item->new_van_id,
            'reason_id' => array_key_exists('reasonId', $data) ? $data['reasonId'] : $item->reason_id,
            'date' => $data['date'] ?? $item->date,
        ]);
        $item->save();

        return $item->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(DriverAndVanSwaping $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'orderId' => $item->order_id,
            'newSalesmanId' => $item->new_salesman_id,
            'oldSalesmanId' => $item->old_salesman_id,
            'oldVanId' => $item->old_van_id,
            'newVanId' => $item->new_van_id,
            'loginUserId' => $item->login_user_id,
            'reasonId' => $item->reason_id,
            'date' => $item->date?->toDateString(),
            'createdAt' => $item->created_at?->toISOString(),
            'updatedAt' => $item->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(DriverAndVanSwaping $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'date' => $item->date?->toDateString(),
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = DriverAndVanSwaping::where('organisation_id', $organisationId);

        if (! empty($filters['old_salesman_id'])) {
            $query->where('old_salesman_id', $filters['old_salesman_id']);
        }

        if (! empty($filters['new_salesman_id'])) {
            $query->where('new_salesman_id', $filters['new_salesman_id']);
        }

        if (! empty($filters['reason_id'])) {
            $query->where('reason_id', $filters['reason_id']);
        }

        return $query;
    }
}
