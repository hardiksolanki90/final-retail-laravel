<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDebitNoteActionRequest;
use App\Http\Requests\StoreDebitNoteRequest;
use App\Http\Requests\UpdateDebitNoteRequest;
use App\Repositories\DebitNoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class DebitNoteController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(DebitNoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('debit-note');
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

    public function store(StoreDebitNoteRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateDebitNoteRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->delete($request);
    }

    public function bulkAction(BulkDebitNoteActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
