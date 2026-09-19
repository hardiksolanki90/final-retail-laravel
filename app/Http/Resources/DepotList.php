<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepotList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'userId' => $this->user_id,
            'regionId' => $this->region_id,
            'areaId' => $this->area_id,
            'depotCode' => $this->depot_code,
            'depotName' => $this->depot_name,
            'depotManager' => $this->depot_manager,
            'depotManagerContact' => $this->depot_manager_contact,
            'status' => (bool) $this->status,
            'region' => $this->relationLoaded('region') && $this->region
                ? ['id' => $this->region->id, 'uuid' => $this->region->uuid, 'name' => $this->region->region_name]
                : null,
            'area' => $this->relationLoaded('area') && $this->area
                ? ['id' => $this->area->id, 'uuid' => $this->area->uuid, 'name' => $this->area->area_name]
                : null,
        ];
    }
}
