<?php

namespace App\Repositories;

use App\Models\CurrencyMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyMasterRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = CurrencyMaster::filter($request->only(['search']))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 20))
            ->through(fn (CurrencyMaster $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'currencyMasters'), 200);
    }

    protected function toSelectOption(CurrencyMaster $currencyMaster): array
    {
        return [
            'id' => $currencyMaster->id,
            'name' => $currencyMaster->name,
            'code' => $currencyMaster->code,
            'symbol' => $currencyMaster->symbol,
            'namePlural' => $currencyMaster->name_plural,
            'symbolNative' => $currencyMaster->symbol_native,
            'decimalDigits' => $currencyMaster->decimal_digits,
            'rounding' => $currencyMaster->rounding,
        ];
    }
}
