<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use App\Repositories\ChannelRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(protected ChannelRepository $channels) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->channels->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->channels->toSelectOption($item))->values(),
            'message' => 'Channels retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->channels->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->channels->toResource($item),
            'message' => 'Channel retrieved successfully.',
        ]);
    }

    public function store(StoreChannelRequest $request): JsonResponse
    {
        $item = $this->channels->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->channels->toResource($item),
            'message' => 'Channel created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateChannelRequest $request): JsonResponse
    {
        $item = $this->channels->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->channels->toResource($item),
            'message' => 'Channel updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->channels->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Channel deleted successfully.']);
    }
}
