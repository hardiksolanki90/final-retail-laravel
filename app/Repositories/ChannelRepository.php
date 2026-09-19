<?php

namespace App\Repositories;

use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use App\Http\Resources\ChannelList;
use App\Http\Resources\ChannelView;
use App\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = Channel::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (Channel $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'channels'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new ChannelView($item))->resolve(),
            'message' => 'Channel retrieved successfully.',
        ]);
    }

    public function store(StoreChannelRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = Channel::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = Channel::create([
            'parent_id' => $parentId,
            'name' => $data['channelName'] ?? $data['name'] ?? '',
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new ChannelView($item))->resolve(),
            'message' => 'Channel created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateChannelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $channel = $this->findByUuid($uuid);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : $channel->parent_id;
        $nodeLevel = $channel->node_level;

        if (array_key_exists('parentId', $data)) {
            if ($parentId) {
                $parent = Channel::where('id', $parentId)->first();
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

        return response()->json([
            'data' => (new ChannelView($channel->fresh()))->resolve(),
            'message' => 'Channel updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Channel deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Channel
    {
        return Channel::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Channel $channel): array
    {
        return [
            'id' => $channel->id,
            'uuid' => $channel->uuid,
            'channelName' => $channel->name,
            'name' => $channel->name,
        ];
    }
}

