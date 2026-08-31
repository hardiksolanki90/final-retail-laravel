<?php

namespace App\Repositories;

use App\Models\VanCategory;
use Illuminate\Support\Collection;

class VanCategoryRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        $query = VanCategory::where('organisation_id', $organisationId);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('id')->get();
    }

    public function toSelectOption(VanCategory $vanCategory): array
    {
        return [
            'id' => $vanCategory->id,
            'uuid' => $vanCategory->uuid,
            'name' => $vanCategory->name,
        ];
    }
}
