<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverAndVanSwapingRequest;
use App\Http\Requests\UpdateDriverAndVanSwapingRequest;
use App\Repositories\DriverAndVanSwapingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class DriverAndVanSwapingController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(DriverAndVanSwapingRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('driver-replacement');
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

    public function store(StoreDriverAndVanSwapingRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateDriverAndVanSwapingRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        return $this->repository->destroyByUuid($uuid);
    }
}
