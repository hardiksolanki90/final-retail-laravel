<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkSalesmanUnloadActionRequest;
use App\Http\Requests\StoreSalesmanUnloadRequest;
use App\Http\Requests\UpdateSalesmanUnloadRequest;
use App\Repositories\SalesmanUnloadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class SalesmanUnloadController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(SalesmanUnloadRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('salesman-unload');
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

    public function show(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->show($uuid, $request);
    }

    public function store(StoreSalesmanUnloadRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateSalesmanUnloadRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkSalesmanUnloadActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
