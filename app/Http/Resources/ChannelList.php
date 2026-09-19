<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelList extends JsonResource
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
            'channelName' => $this->name,
            'name' => $this->name,
            'parentId' => $this->parent_id,
            'nodeLevel' => $this->node_level,
            'status' => (bool) $this->status,
        ];
    }
}
