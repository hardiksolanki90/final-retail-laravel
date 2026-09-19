<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Requests\UpdatePaymentTermRequest;
use App\Repositories\PaymentTermRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PaymentTermController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(PaymentTermRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('payment-term');
    }

    public function all(Request $request): JsonResponse
    {
        return $this->repository->all($request);
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StorePaymentTermRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdatePaymentTermRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }
}
