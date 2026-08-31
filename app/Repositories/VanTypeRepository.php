<?php

namespace App\Repositories;

use App\Models\VanType;
use Illuminate\Support\Collection;

class VanTypeRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        $query = VanType::where('organisation_id', $organisationId);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('id')->get();
    }

    public function toSelectOption(VanType $vanType): array
    {
        return [
            'id' => $vanType->id,
            'uuid' => $vanType->uuid,
            'name' => $vanType->name,
        ];
    }
}
