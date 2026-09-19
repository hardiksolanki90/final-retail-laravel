<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkGoodReceiptNoteActionRequest;
use App\Http\Requests\StoreGoodReceiptNoteRequest;
use App\Http\Requests\UpdateGoodReceiptNoteRequest;
use App\Repositories\GoodReceiptNoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class GoodReceiptNoteController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(GoodReceiptNoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('grn');
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

    public function store(StoreGoodReceiptNoteRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateGoodReceiptNoteRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->delete($request);
    }

    public function bulkAction(BulkGoodReceiptNoteActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
