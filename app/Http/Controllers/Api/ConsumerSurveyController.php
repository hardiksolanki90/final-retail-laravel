<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkConsumerSurveyActionRequest;
use App\Http\Requests\StoreConsumerSurveyRequest;
use App\Http\Requests\UpdateConsumerSurveyRequest;
use App\Repositories\ConsumerSurveyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ConsumerSurveyController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(ConsumerSurveyRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('consumer-survey');
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

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StoreConsumerSurveyRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateConsumerSurveyRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkConsumerSurveyActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
