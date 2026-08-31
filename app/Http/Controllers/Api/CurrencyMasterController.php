<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CurrencyMasterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyMasterController extends Controller
{
    public function __construct(protected CurrencyMasterRepository $currencyMasters) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->currencyMasters->all();

        return response()->json([
            'data' => $items->map(fn ($item) => $this->currencyMasters->toSelectOption($item))->values(),
            'message' => 'Currency masters retrieved successfully.',
        ]);
    }
}
