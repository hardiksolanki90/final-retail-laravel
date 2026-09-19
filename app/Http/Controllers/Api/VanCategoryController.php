<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVanCategoryRequest;
use App\Repositories\VanCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class VanCategoryController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(VanCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('van-category');
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function store(StoreVanCategoryRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }
}
