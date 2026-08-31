<?php

namespace App\Repositories;

use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerTypeRepository
{
    public function all(array $filters = []): Collection
    {
        return $this->filtered($filters)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid): CustomerType
    {
        return CustomerType::where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): CustomerType
    {
        return CustomerType::create([
            'customer_type_code' => $data['customerTypeCode'] ?? $data['code'] ?? '',
            'customer_type_name' => $data['name'] ?? $data['customerTypeName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data): CustomerType
    {
        $customerType = $this->findByUuid($uuid);

        $customerType->fill([
            'customer_type_code' => $data['customerTypeCode'] ?? $data['code'] ?? $customerType->customer_type_code,
            'customer_type_name' => $data['name'] ?? $data['customerTypeName'] ?? $customerType->customer_type_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $customerType->status,
        ]);
        $customerType->save();

        return $customerType->fresh();
    }

    public function delete(string $uuid): void
    {
        $this->findByUuid($uuid)->delete();
    }

    public function toResource(CustomerType $customerType): array
    {
        return [
            'id' => $customerType->id,
            'uuid' => $customerType->uuid,
            'name' => $customerType->customer_type_name,
            'customerTypeCode' => $customerType->customer_type_code,
            'customerTypeName' => $customerType->customer_type_name,
            'status' => (bool) $customerType->status,
            'createdAt' => $customerType->created_at?->toISOString(),
            'updatedAt' => $customerType->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(CustomerType $customerType): array
    {
        return [
            'id' => $customerType->id,
            'uuid' => $customerType->uuid,
            'name' => $customerType->customer_type_name,
        ];
    }

    protected function filtered(array $filters): Builder
    {
        $query = CustomerType::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('customer_type_code', 'like', "%{$search}%")
                    ->orWhere('customer_type_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
