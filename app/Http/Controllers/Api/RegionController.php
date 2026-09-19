<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Repositories\RegionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class RegionController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(RegionRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('region');
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

    public function store(StoreRegionRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateRegionRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        return $this->repository->destroyByUuid($uuid);
    }
}
