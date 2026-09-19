<?php

namespace App\Repositories;

use App\Http\Requests\StoreReasonTypeRequest;
use App\Http\Requests\UpdateReasonTypeRequest;
use App\Models\ReasonType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReasonTypeRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = ReasonType::where('organisation_id', $request->user()->organisation_id)
            ->filter($request->only(['search', 'type', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (ReasonType $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'reasonTypes'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = ReasonType::where('organisation_id', $request->user()->organisation_id)
            ->filter($request->only(['search', 'type', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (ReasonType $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'reasonTypes'), 200);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Reason type retrieved successfully.',
        ]);
    }

    public function store(StoreReasonTypeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = ReasonType::create([
            'organisation_id' => $request->user()->organisation_id,
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? '',
            'code' => $data['code'] ?? null,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Reason type created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateReasonTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reasonType = $this->findByUuid($uuid, $request->user()->organisation_id);

        $reasonType->fill([
            'name' => $data['name'] ?? $reasonType->name,
            'type' => $data['type'] ?? $reasonType->type,
            'code' => array_key_exists('code', $data) ? $data['code'] : $reasonType->code,
            'status' => array_key_exists('status', $data) ? $data['status'] : $reasonType->status,
        ]);
        $reasonType->save();

        return response()->json([
            'data' => $this->toResource($reasonType->fresh()),
            'message' => 'Reason type updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid, Request $request): JsonResponse
    {
        $this->findByUuid($uuid, $request->user()->organisation_id)->delete();

        return response()->json(['message' => 'Reason type deleted successfully.']);
    }

    protected function findByUuid(string $uuid, int $organisationId): ReasonType
    {
        return ReasonType::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toResource(ReasonType $reasonType): array
    {
        return [
            'id' => $reasonType->id,
            'uuid' => $reasonType->uuid,
            'name' => $reasonType->name,
            'type' => $reasonType->type,
            'code' => $reasonType->code,
            'status' => (bool) $reasonType->status,
        ];
    }

    protected function toSelectOption(ReasonType $reasonType): array
    {
        return [
            'id' => $reasonType->id,
            'uuid' => $reasonType->uuid,
            'name' => $reasonType->name,
            'type' => $reasonType->type,
        ];
    }
}
