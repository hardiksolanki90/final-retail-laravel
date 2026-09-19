<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkJourneyPlanActionRequest;
use App\Http\Requests\StoreJourneyPlanRequest;
use App\Http\Requests\UpdateJourneyPlanRequest;
use App\Repositories\JourneyPlanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class JourneyPlanController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(JourneyPlanRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('journey-plan');
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

    public function store(StoreJourneyPlanRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateJourneyPlanRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->repository->destroy($request);
    }

    public function bulkAction(BulkJourneyPlanActionRequest $request): JsonResponse
    {
        return $this->repository->bulkAction($request);
    }
}
