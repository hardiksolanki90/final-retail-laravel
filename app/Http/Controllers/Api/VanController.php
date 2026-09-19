<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVanRequest;
use App\Http\Requests\UpdateVanRequest;
use App\Repositories\VanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class VanController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(VanRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('van');
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

    public function store(StoreVanRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateVanRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        return $this->repository->destroyByUuid($uuid);
    }
}
