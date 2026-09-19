<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Repositories\OrganisationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrganisationController extends Controller implements HasMiddleware
{
    protected $repository;

    public function __construct(OrganisationRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function middleware(): array
    {
        return permissionMiddleware('organisation');
    }

    public function current(Request $request): JsonResponse
    {
        return $this->repository->current($request);
    }

    public function details(Request $request): JsonResponse
    {
        return $this->repository->details($request);
    }

    public function update(UpdateOrganisationRequest $request): JsonResponse
    {
        return $this->repository->update($request);
    }
}
