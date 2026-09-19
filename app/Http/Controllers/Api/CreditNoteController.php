<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkCreditNoteActionRequest;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Requests\UpdateCreditNoteRequest;
use App\Repositories\CreditNoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CreditNoteController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(CreditNoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('credit-notes');
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

    public function store(StoreCreditNoteRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateCreditNoteRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->delete($request);
    }

    public function bulkAction(BulkCreditNoteActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
