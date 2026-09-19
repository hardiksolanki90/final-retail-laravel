<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVanTypeRequest;
use App\Repositories\VanTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class VanTypeController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(VanTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('van-type');
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function store(StoreVanTypeRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }
}
