<?php

namespace App\Repositories;

use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Models\Organisation;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateRepository
{
    public function types(Request $request): JsonResponse
    {
        $organisation = Organisation::find($request->user()->organisation_id);
        $profile = $organisation?->resolveTaxProfile();

        return response()->json([
            'data' => $profile['components'] ?? [],
            'message' => 'Tax types retrieved successfully.',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $paginated = TaxRate::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn(TaxRate $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'taxRates'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = TaxRate::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn(TaxRate $item) => $this->toSelectOption($item))->values(),
            'message' => 'Tax rates retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Tax rate retrieved successfully.',
        ]);
    }

    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = TaxRate::create([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'Tax rate created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateTaxRateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $taxRate = $this->findByUuid($uuid);

        $taxRate->fill([
            'name' => $data['name'] ?? $taxRate->name,
            'rate' => $data['rate'] ?? $taxRate->rate,
            'type' => $data['type'] ?? $taxRate->type,
            'description' => $data['description'] ?? $taxRate->description,
        ]);
        $taxRate->save();

        return response()->json([
            'data' => $this->toResource($taxRate->fresh()),
            'message' => 'Tax rate updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Tax rate deleted successfully.']);
    }

    protected function findByUuid(string $uuid): TaxRate
    {
        return TaxRate::where('uuid', $uuid)->firstOrFail();
    }

    public function toResource(TaxRate $taxRate): array
    {
        return [
            'id' => $taxRate->id,
            'uuid' => $taxRate->uuid,
            'name' => $taxRate->name,
            'rate' => $taxRate->rate,
            'type' => $taxRate->type,
            'description' => $taxRate->description,
        ];
    }

    public function toSelectOption(TaxRate $taxRate): array
    {
        return [
            'id' => $taxRate->id,
            'uuid' => $taxRate->uuid,
            'name' => $taxRate->name,
            'type' => $taxRate->type,
        ];
    }
}
