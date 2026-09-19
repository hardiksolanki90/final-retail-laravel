<?php

namespace App\Repositories;

use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyList;
use App\Http\Resources\CurrencyView;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = Currency::filter($request->only(['search']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Currency $item) => (new CurrencyList($item))->resolve());

        return response()->json(paginated($paginated, 'currencies'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Currency::filter($request->only(['search']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (Currency $item) => $this->toSelectOption($item))->values(),
            'message' => 'Currencies retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new CurrencyView($item))->resolve(),
            'message' => 'Currency retrieved successfully.',
        ]);
    }

    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $isDefault = $data['defaultCurrency'] ?? false;

        $item = Currency::create([
            'currency_master_id' => $data['currencyMasterId'] ?? null,
            'name' => $data['name'] ?? '',
            'symbol' => $data['symbol'] ?? '',
            'code' => $data['code'] ?? '',
            'name_plural' => $data['namePlural'] ?? '',
            'symbol_native' => $data['symbolNative'] ?? '',
            'decimal_digits' => $data['decimalDigits'] ?? 0,
            'rounding' => $data['rounding'] ?? 0,
            'default_currency' => $isDefault,
            'format' => $data['format'] ?? '1,234,567.89',
        ]);

        if ($isDefault) {
            $this->unsetOtherDefaults($item);
        }

        return response()->json([
            'data' => (new CurrencyView($item))->resolve(),
            'message' => 'Currency created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCurrencyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $currency = $this->findByUuid($uuid);

        $currency->fill([
            'currency_master_id' => array_key_exists('currencyMasterId', $data) ? $data['currencyMasterId'] : $currency->currency_master_id,
            'name' => array_key_exists('name', $data) ? $data['name'] : $currency->name,
            'symbol' => array_key_exists('symbol', $data) ? $data['symbol'] : $currency->symbol,
            'code' => array_key_exists('code', $data) ? $data['code'] : $currency->code,
            'name_plural' => array_key_exists('namePlural', $data) ? $data['namePlural'] : $currency->name_plural,
            'symbol_native' => array_key_exists('symbolNative', $data) ? $data['symbolNative'] : $currency->symbol_native,
            'decimal_digits' => array_key_exists('decimalDigits', $data) ? $data['decimalDigits'] : $currency->decimal_digits,
            'rounding' => array_key_exists('rounding', $data) ? $data['rounding'] : $currency->rounding,
            'default_currency' => array_key_exists('defaultCurrency', $data) ? $data['defaultCurrency'] : $currency->default_currency,
            'format' => array_key_exists('format', $data) ? $data['format'] : $currency->format,
        ]);
        $currency->save();

        if ($currency->default_currency) {
            $this->unsetOtherDefaults($currency);
        }

        return response()->json([
            'data' => (new CurrencyView($currency->fresh()))->resolve(),
            'message' => 'Currency updated successfully.',
        ]);
    }

    /**
     * Only one currency can be default per org — flip every other one off
     * whenever this currency is (re)marked as the default.
     */
    protected function unsetOtherDefaults(Currency $currency): void
    {
        Currency::where('organisation_id', $currency->organisation_id)
            ->where('id', '!=', $currency->id)
            ->update(['default_currency' => false]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Currency deleted successfully.']);
    }

    protected function findByUuid(string $uuid): Currency
    {
        return Currency::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(Currency $currency): array
    {
        return [
            'id' => $currency->id,
            'uuid' => $currency->uuid,
            'code' => $currency->code,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
        ];
    }
}

