<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkItemActionRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Repositories\ItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ItemController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('item');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function withStock(Request $request): JsonResponse
    {
        return $this->repository->withStock($request);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->show($uuid, $request);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateItemRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkItemActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
