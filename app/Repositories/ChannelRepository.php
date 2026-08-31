<?php

namespace App\Repositories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ChannelRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Channel
    {
        return Channel::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Channel
    {
        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = Channel::where('organisation_id', $organisationId)
                ->where('id', $parentId)
                ->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        return Channel::create([
            'organisation_id' => $organisationId,
            'parent_id' => $parentId,
            'name' => $data['channelName'] ?? $data['name'] ?? '',
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Channel
    {
        $channel = $this->findByUuid($uuid, $organisationId);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $channel->parent_id;
        $nodeLevel = $channel->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = Channel::where('organisation_id', $organisationId)
                    ->where('id', $parentId)
                    ->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $channel->fill([
            'parent_id' => $parentId,
            'name' => $data['channelName'] ?? $data['name'] ?? $channel->name,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => array_key_exists('status', $data) ? $data['status'] : $channel->status,
        ]);
        $channel->save();

        return $channel->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Channel $channel): array
    {
        return [
            'id' => $channel->id,
            'uuid' => $channel->uuid,
            'channelName' => $channel->name,
            'name' => $channel->name,
            'parentId' => $channel->parent_id,
            'nodeLevel' => $channel->node_level,
            'status' => (bool) $channel->status,
            'createdAt' => $channel->created_at?->toISOString(),
            'updatedAt' => $channel->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Channel $channel): array
    {
        return [
            'id' => $channel->id,
            'uuid' => $channel->uuid,
            'channelName' => $channel->name,
            'name' => $channel->name,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Channel::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
