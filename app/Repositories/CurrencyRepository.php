<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CurrencyRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Currency
    {
        return Currency::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Currency
    {
        return Currency::create([
            'organisation_id' => $organisationId,
            'currency_master_id' => $data['currencyMasterId'] ?? null,
            'name' => $data['name'] ?? '',
            'symbol' => $data['symbol'] ?? '',
            'code' => $data['code'] ?? '',
            'name_plural' => $data['namePlural'] ?? '',
            'symbol_native' => $data['symbolNative'] ?? '',
            'decimal_digits' => $data['decimalDigits'] ?? 0,
            'rounding' => $data['rounding'] ?? 0,
            'default_currency' => $data['defaultCurrency'] ?? false,
            'format' => $data['format'] ?? '1,234,567.89',
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): Currency
    {
        $currency = $this->findByUuid($uuid, $organisationId);

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

        return $currency->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(Currency $currency): array
    {
        return [
            'id' => $currency->id,
            'uuid' => $currency->uuid,
            'currencyMasterId' => $currency->currency_master_id,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
            'code' => $currency->code,
            'namePlural' => $currency->name_plural,
            'symbolNative' => $currency->symbol_native,
            'decimalDigits' => $currency->decimal_digits,
            'rounding' => $currency->rounding,
            'defaultCurrency' => (bool) $currency->default_currency,
            'format' => $currency->format,
            'createdAt' => $currency->created_at?->toISOString(),
            'updatedAt' => $currency->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(Currency $currency): array
    {
        return [
            'id' => $currency->id,
            'uuid' => $currency->uuid,
            'code' => $currency->code,
            'name' => $currency->name,
            'symbol' => $currency->symbol,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Currency::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
