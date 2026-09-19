<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaList extends JsonResource
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
            'areaCode' => $this->area_code,
            'code' => $this->area_code,
            'areaName' => $this->area_name,
            'name' => $this->area_name,
            'parentId' => $this->parent_id,
            'nodeLevel' => $this->node_level,
            'status' => (bool) $this->status,
        ];
    }
}
