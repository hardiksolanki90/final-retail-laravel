<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeliveryActionRequest;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Repositories\DeliveryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class DeliveryController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(DeliveryRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('delivery');
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

    public function store(StoreDeliveryRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateDeliveryRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->delete($request);
    }

    public function bulkAction(BulkDeliveryActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
