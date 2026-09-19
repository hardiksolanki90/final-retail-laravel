<?php

namespace App\Repositories;

use App\Http\Requests\BulkDivisionActionRequest;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use App\Http\Resources\DivisionList;
use App\Http\Resources\DivisionView;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Division::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Division $item) => (new DivisionList($item))->resolve());

        return response()->json(paginated($paginated, 'divisions'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Division::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Division $item) => $this->toSelectOption($item))->values(),
            'message' => 'Divisions retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new DivisionView($item))->resolve(),
            'message' => 'Division retrieved successfully.',
        ]);
    }

    public function store(StoreDivisionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = Division::create([
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? '',
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new DivisionView($item))->resolve(),
            'message' => 'Division created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateDivisionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $division = $this->findByUuid($uuid);

        $division->fill([
            'code' => array_key_exists('code', $data) ? $data['code'] : $division->code,
            'name' => $data['name'] ?? $division->name,
            'status' => array_key_exists('status', $data) ? $data['status'] : $division->status,
        ]);
        $division->save();

        return response()->json([
            'data' => (new DivisionView($division->fresh()))->resolve(),
            'message' => 'Division updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Division deleted successfully.']);
    }

    public function bulkAction(BulkDivisionActionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $query = Division::whereIn('uuid', $data['uuids']);

        match ($data['action']) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    protected function findByUuid(string $uuid): Division
    {
        return Division::where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function toSelectOption(Division $division): array
    {
        return [
            'value' => $division->uuid,
            'label' => $division->code ? "{$division->code} - {$division->name}" : $division->name,
        ];
    }
}

