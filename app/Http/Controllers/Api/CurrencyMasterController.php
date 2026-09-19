<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CurrencyMasterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CurrencyMasterController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(CurrencyMasterRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('currency-master');
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }
}
