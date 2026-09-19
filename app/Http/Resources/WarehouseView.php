<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseView extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'manager' => $this->manager,
            'managerPhone' => $this->manager_phone,
            'isMain' => (bool) $this->is_main,
            'locType' => $this->loc_type,
            'lat' => $this->lat,
            'lang' => $this->lang,
            'depotId' => $this->depot_id,
            'routeId' => $this->route_id,
            'parentWarehouseId' => $this->parent_warehouse_id,
            'status' => (bool) $this->status,
        ];
    }
}
