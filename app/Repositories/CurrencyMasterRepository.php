<?php

namespace App\Repositories;

use App\Models\CurrencyMaster;
use Illuminate\Support\Collection;

class CurrencyMasterRepository
{
    public function all(): Collection
    {
        return CurrencyMaster::orderBy('id')->get();
    }

    public function toSelectOption(CurrencyMaster $currencyMaster): array
    {
        return [
            'id' => $currencyMaster->id,
            'name' => $currencyMaster->name,
            'code' => $currencyMaster->code,
            'symbol' => $currencyMaster->symbol,
        ];
    }
}
