<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInviteUserRequest;
use App\Http\Requests\UpdateInviteUserRequest;
use App\Repositories\InviteUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class InviteUserController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(InviteUserRepository $repository)
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

    public function show(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->show($uuid, $request);
    }

    public function store(StoreInviteUserRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateInviteUserRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->destroy($uuid, $request);
    }
}
