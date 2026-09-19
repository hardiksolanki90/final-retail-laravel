<?php

namespace App\Repositories;

use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CountryList;
use App\Http\Resources\CountryView;
use App\Models\Country;
use App\Models\CountryMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Country::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Country $item) => (new CountryList($item))->resolve());

        return response()->json(paginated($paginated, 'countries'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $paginated = Country::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (Country $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'countries'), 200);
    }

    /**
     * Global ISO reference list — used by the public registration form
     * (which runs before any organisation/tenant exists) and by any
     * authenticated "pick a country" selector.
     */
    public function publicList(): JsonResponse
    {
        $items = CountryMaster::orderBy('name')->get();

        return response()->json([
            'data' => $items->map(fn (CountryMaster $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'countryCode' => $item->country_code,
            ])->values(),
            'message' => 'Countries retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new CountryView($item))->resolve(),
            'message' => 'Country retrieved successfully.',
        ]);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $countryMaster = CountryMaster::findOrFail($data['countryMasterId']);

        $item = Country::create(array_merge($countryMaster->toCountrySnapshot(), [
            'status' => $data['status'] ?? true,
        ]));

        return response()->json([
            'data' => (new CountryView($item))->resolve(),
            'message' => 'Country created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCountryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $country = $this->findByUuid($uuid);

        if (isset($data['countryMasterId'])) {
            $countryMaster = CountryMaster::findOrFail($data['countryMasterId']);
            $country->fill($countryMaster->toCountrySnapshot());
        }

        $country->fill([
            'status' => array_key_exists('status', $data) ? $data['status'] : $country->status,
        ]);
        $country->save();

        return response()->json([
            'data' => (new CountryView($country->fresh()))->resolve(),
            'message' => 'Country updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Country deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Country
    {
        return Country::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Country $country): array
    {
        return [
            'id' => $country->id,
            'uuid' => $country->uuid,
            'name' => $country->name,
            'countryCode' => $country->country_code,
        ];
    }
}

