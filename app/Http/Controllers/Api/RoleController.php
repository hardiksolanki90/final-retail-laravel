<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkRoleActionRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class RoleController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('users-roles');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->show($uuid, $request);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateRoleRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkRoleActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
