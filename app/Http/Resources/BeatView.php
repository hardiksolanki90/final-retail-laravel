<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeatView extends JsonResource
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
            'beatCode' => $this->beat_code,
            'code' => $this->beat_code,
            'beatName' => $this->beat_name,
            'name' => $this->beat_name,
            'areaId' => $this->area_id,
            'area' => $this->relationLoaded('area') && $this->area
                ? [
                    'id' => $this->area->id,
                    'uuid' => $this->area->uuid,
                    'code' => $this->area->area_code,
                    'name' => $this->area->area_name,
                ]
                : null,
            'status' => (bool) $this->status,
        ];
    }
}
