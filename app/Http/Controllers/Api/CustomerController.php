<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkCustomerActionRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CustomerController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('customer');
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
        return $this->repository->show($uuid);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateCustomerRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->destroy($uuid);
    }

    public function bulkAction(BulkCustomerActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }

    public function sales(string $uuid, Request $request): JsonResponse
    {
        return $this->repository->sales($uuid);
    }

    public function bySalesman(int $salesmanId, Request $request): JsonResponse
    {
        return $this->repository->bySalesman($salesmanId);
    }
}
