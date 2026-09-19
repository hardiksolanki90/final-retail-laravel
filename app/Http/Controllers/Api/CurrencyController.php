<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CurrencyController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(CurrencyRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('currency');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateCurrencyRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        return $this->repository->destroyByUuid($uuid);
    }
}
