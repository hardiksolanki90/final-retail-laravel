<?php

namespace App\Repositories;

use App\Http\Requests\StoreMerchandiserReplacementRequest;
use App\Http\Requests\UpdateMerchandiserReplacementRequest;
use App\Models\MerchandiserReplacement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchandiserReplacementRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = MerchandiserReplacement::filter($request->only(['old_salesman_id', 'new_salesman_id']))
            ->with(['oldSalesman', 'newSalesman'])
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (MerchandiserReplacement $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'merchandiserReplacements'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = MerchandiserReplacement::filter($request->only(['old_salesman_id', 'new_salesman_id']))
            ->with(['oldSalesman', 'newSalesman'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (MerchandiserReplacement $item) => $this->toSelectOption($item))->values(),
            'message' => 'Merchandiser replacements retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Merchandiser replacement retrieved successfully.',
        ]);
    }

    public function store(StoreMerchandiserReplacementRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = MerchandiserReplacement::create([
            'old_salesman_id' => $data['oldSalesmanId'],
            'new_salesman_id' => $data['newSalesmanId'],
            'type' => $data['type'],
            'added_on' => $data['addedOn'],
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Merchandiser replacement created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateMerchandiserReplacementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->findByUuid($uuid);

        $item->fill([
            'old_salesman_id' => $data['oldSalesmanId'] ?? $item->old_salesman_id,
            'new_salesman_id' => $data['newSalesmanId'] ?? $item->new_salesman_id,
            'type' => $data['type'] ?? $item->type,
            'added_on' => $data['addedOn'] ?? $item->added_on,
        ]);
        $item->save();

        return response()->json([
            'data' => $this->toResource($item->fresh()),
            'message' => 'Merchandiser replacement updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Merchandiser replacement deleted successfully.']);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Merchandiser replacement deleted successfully.']);
    }

    protected function findByUuid(string $uuid): MerchandiserReplacement
    {
        return MerchandiserReplacement::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(MerchandiserReplacement $item): array
    {
        $resource = [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'oldSalesmanId' => $item->old_salesman_id,
            'newSalesmanId' => $item->new_salesman_id,
            'type' => $item->type,
            'addedOn' => $item->added_on?->toDateString(),
        ];

        if ($item->relationLoaded('oldSalesman') && $item->oldSalesman) {
            $resource['oldSalesman'] = [
                'id' => $item->oldSalesman->id,
                'name' => trim($item->oldSalesman->firstname.' '.$item->oldSalesman->lastname),
            ];
        }

        if ($item->relationLoaded('newSalesman') && $item->newSalesman) {
            $resource['newSalesman'] = [
                'id' => $item->newSalesman->id,
                'name' => trim($item->newSalesman->firstname.' '.$item->newSalesman->lastname),
            ];
        }

        return $resource;
    }

    protected function toSelectOption(MerchandiserReplacement $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'type' => $item->type,
            'addedOn' => $item->added_on?->toDateString(),
        ];
    }
}
