<?php

namespace App\Repositories;

use App\Models\CustomerCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerCategoryRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): CustomerCategory
    {
        return CustomerCategory::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): CustomerCategory
    {
        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = CustomerCategory::where('organisation_id', $organisationId)
                ->where('id', $parentId)
                ->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        return CustomerCategory::create([
            'organisation_id' => $organisationId,
            'customer_category_code' => $data['customerCategoryCode'] ?? $data['code'] ?? '',
            'parent_id' => $parentId,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'customer_category_name' => $data['categoryName'] ?? $data['customerCategoryName'] ?? '',
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): CustomerCategory
    {
        $category = $this->findByUuid($uuid, $organisationId);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $category->parent_id;
        $nodeLevel = $category->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = CustomerCategory::where('organisation_id', $organisationId)
                    ->where('id', $parentId)
                    ->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $category->fill([
            'customer_category_code' => $data['customerCategoryCode'] ?? $data['code'] ?? $category->customer_category_code,
            'parent_id' => $parentId,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'customer_category_name' => $data['categoryName'] ?? $data['customerCategoryName'] ?? $category->customer_category_name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $category->status,
        ]);
        $category->save();

        return $category->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(CustomerCategory $category): array
    {
        return [
            'id' => $category->id,
            'uuid' => $category->uuid,
            'categoryName' => $category->customer_category_name,
            'customerCategoryCode' => $category->customer_category_code,
            'customerCategoryName' => $category->customer_category_name,
            'parentId' => $category->parent_id,
            'nodeLevel' => $category->node_level,
            'status' => (bool) $category->status,
            'createdAt' => $category->created_at?->toISOString(),
            'updatedAt' => $category->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(CustomerCategory $category): array
    {
        return [
            'id' => $category->id,
            'uuid' => $category->uuid,
            'categoryName' => $category->customer_category_name,
            'name' => $category->customer_category_name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = CustomerCategory::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('customer_category_code', 'like', "%{$search}%")
                    ->orWhere('customer_category_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
