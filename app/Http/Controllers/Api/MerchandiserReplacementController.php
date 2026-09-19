<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchandiserReplacementRequest;
use App\Http\Requests\UpdateMerchandiserReplacementRequest;
use App\Repositories\MerchandiserReplacementRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class MerchandiserReplacementController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(MerchandiserReplacementRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('merchandiser-replacement');
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

    public function store(StoreMerchandiserReplacementRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateMerchandiserReplacementRequest $request): JsonResponse
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
