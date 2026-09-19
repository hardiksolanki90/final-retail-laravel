<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkPalletActionRequest;
use App\Http\Requests\StorePalletRequest;
use App\Repositories\PalletRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PalletController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(PalletRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('pallet');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StorePalletRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkPalletActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
