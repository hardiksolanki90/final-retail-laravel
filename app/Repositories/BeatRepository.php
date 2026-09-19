<?php

namespace App\Repositories;

use App\Http\Requests\StoreBeatRequest;
use App\Http\Requests\UpdateBeatRequest;
use App\Http\Resources\BeatList;
use App\Http\Resources\BeatView;
use App\Models\Beat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeatRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Beat::filter($request->only(['search', 'area_id', 'status']))
            ->with(['area'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Beat $item) => (new BeatList($item))->resolve());

        return response()->json(paginated($paginated, 'beats'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Beat::filter($request->only(['search', 'area_id', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Beat $item) => $this->toSelectOption($item))->values(),
            'message' => 'Beats retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new BeatView($item->load(['area'])))->resolve(),
            'message' => 'Beat retrieved successfully.',
        ]);
    }

    public function store(StoreBeatRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Beat::create([
            'beat_code' => $data['beatCode'] ?? $data['beat_code'] ?? $data['code'] ?? '',
            'beat_name' => $data['beatName'] ?? $data['beat_name'] ?? $data['name'] ?? '',
            'area_id' => $data['areaId'] ?? $data['area_id'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new BeatView($item->fresh(['area'])))->resolve(),
            'message' => 'Beat created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateBeatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $beat = $this->findByUuid($uuid);

        $beat->fill([
            'beat_code' => $data['beatCode'] ?? $data['beat_code'] ?? $data['code'] ?? $beat->beat_code,
            'beat_name' => $data['beatName'] ?? $data['beat_name'] ?? $data['name'] ?? $beat->beat_name,
            'area_id' => array_key_exists('areaId', $data)
                ? $data['areaId']
                : (array_key_exists('area_id', $data) ? $data['area_id'] : $beat->area_id),
            'status' => array_key_exists('status', $data) ? $data['status'] : $beat->status,
        ]);
        $beat->save();

        return response()->json([
            'data' => (new BeatView($beat->fresh(['area'])))->resolve(),
            'message' => 'Beat updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Beat deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Beat
    {
        return Beat::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Beat $beat): array
    {
        return [
            'id' => $beat->id,
            'uuid' => $beat->uuid,
            'beatCode' => $beat->beat_code,
            'code' => $beat->beat_code,
            'beatName' => $beat->beat_name,
            'name' => $beat->beat_name,
        ];
    }
}
