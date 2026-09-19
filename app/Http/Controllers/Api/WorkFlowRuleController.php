<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkFlowRuleRequest;
use App\Http\Requests\UpdateWorkFlowRuleRequest;
use App\Repositories\WorkFlowRuleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class WorkFlowRuleController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(WorkFlowRuleRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('preferences');
    }

    public function list(Request $request): JsonResponse
    {
        return $this->repository->list($request);
    }

    public function show(string $uuid): JsonResponse
    {
        return $this->repository->show($uuid);
    }

    public function store(StoreWorkFlowRuleRequest $request): JsonResponse
    {
        return $this->repository->store($request);
    }

    public function update(string $uuid, UpdateWorkFlowRuleRequest $request): JsonResponse
    {
        return $this->repository->update($uuid, $request);
    }

    public function destroy(string $uuid): JsonResponse
    {
        return $this->repository->destroy($uuid);
    }

    public function approverOptions(Request $request): JsonResponse
    {
        return $this->repository->approverOptions($request);
    }
}
