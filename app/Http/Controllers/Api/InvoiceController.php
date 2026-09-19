<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkInvoiceActionRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class InvoiceController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('invoice');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->repository->search($request);
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateInvoiceRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->delete($request);
    }

    public function bulkAction(BulkInvoiceActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
