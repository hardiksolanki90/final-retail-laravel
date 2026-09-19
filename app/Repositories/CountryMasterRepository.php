<?php

namespace App\Repositories;

use App\Models\CountryMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryMasterRepository
{
    /**
     * Paginated ISO reference list — feeds "pick a country" selects
     * (Organisation profile, Settings > Country) as opposed to an
     * organisation's own already-selected countries (CountryRepository::all).
     * Callers that need the full list in one shot (e.g. useCountryMasters)
     * just pass a large per_page.
     */
    public function all(Request $request): JsonResponse
    {
        $search = $request->input('search');

        $paginated = CountryMaster::when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%")
                    ->orWhere('currency_code', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 20))
            ->through(fn (CountryMaster $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'countryMasters'), 200);
    }

    protected function toSelectOption(CountryMaster $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'countryCode' => $item->country_code,
            'dialCode' => $item->dial_code,
            'currency' => $item->currency,
            'currencyCode' => $item->currency_code,
            'currencySymbol' => $item->currency_symbol,
            'taxProfile' => $item->tax_system ? $item->toTaxProfileResource() : null,
        ];
    }
}
