<?php

namespace App\Repositories;

use App\Http\Requests\StoreSalesOrganisationRequest;
use App\Http\Requests\UpdateSalesOrganisationRequest;
use App\Http\Resources\SalesOrganisationList;
use App\Http\Resources\SalesOrganisationView;
use App\Models\SalesOrganisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrganisationRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = SalesOrganisation::filter($request->only(['search', 'status', 'parent_id']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (SalesOrganisation $item) => (new SalesOrganisationList($item))->resolve());

        return response()->json(paginated($paginated, 'salesOrganisations'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = SalesOrganisation::filter($request->only(['search', 'status', 'parent_id']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (SalesOrganisation $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'salesOrganisations'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new SalesOrganisationView($item))->resolve(),
            'message' => 'Sales organisation retrieved successfully.',
        ]);
    }

    public function store(StoreSalesOrganisationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parentId = $data['parentId'] ?? $data['parent_id'] ?? null;
        $nodeLevel = 0;

        if ($parentId) {
            $parent = SalesOrganisation::where('id', $parentId)->first();
            $nodeLevel = $parent ? $parent->node_level + 1 : 0;
        }

        $item = SalesOrganisation::create([
            'parent_id' => $parentId,
            'name' => $data['salesOrganisationName'] ?? $data['name'] ?? '',
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new SalesOrganisationView($item))->resolve(),
            'message' => 'Sales organisation created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateSalesOrganisationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->findByUuid($uuid);

        $parentId = array_key_exists('parentId', $data) ? $data['parentId'] : (array_key_exists('parent_id', $data) ? $data['parent_id'] : $item->parent_id);
        $nodeLevel = $item->node_level;

        if (array_key_exists('parentId', $data) || array_key_exists('parent_id', $data)) {
            if ($parentId) {
                $parent = SalesOrganisation::where('id', $parentId)->first();
                $nodeLevel = $parent ? $parent->node_level + 1 : 0;
            } else {
                $nodeLevel = 0;
            }
        }

        $item->fill([
            'parent_id' => $parentId,
            'name' => $data['salesOrganisationName'] ?? $data['name'] ?? $item->name,
            'node_level' => $data['nodeLevel'] ?? $nodeLevel,
            'status' => array_key_exists('status', $data) ? $data['status'] : $item->status,
        ]);
        $item->save();

        return response()->json([
            'data' => (new SalesOrganisationView($item->fresh()))->resolve(),
            'message' => 'Sales organisation updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Sales organisation deleted successfully.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Sales organisation deleted successfully.']);
    }

    protected function findByUuid(string $uuid): SalesOrganisation
    {
        return SalesOrganisation::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(SalesOrganisation $item): array
    {
        return [
            'id' => $item->id,
            'uuid' => $item->uuid,
            'name' => $item->name,
            'salesOrganisationName' => $item->name,
            'value' => (string) $item->id,
            'label' => $item->name,
        ];
    }
}
