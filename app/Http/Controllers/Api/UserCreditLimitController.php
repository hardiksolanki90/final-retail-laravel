<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserCreditLimitRequest;
use App\Http\Requests\UpdateUserCreditLimitRequest;
use App\Repositories\UserCreditLimitRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCreditLimitController extends Controller
{
    public function __construct(protected UserCreditLimitRepository $userCreditLimits) {}

    public function list(Request $request): JsonResponse
    {
        $paginated = $this->userCreditLimits->list(
            $request->only(['user_id', 'credit_limit_type']),
            $request->user()->organisation_id,
            (int) $request->input('per_page', 15),
        );

        return response()->json([
            'data' => $paginated->getCollection()->map(fn ($item) => $this->userCreditLimits->toResource($item))->values(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
            'message' => 'User credit limits retrieved successfully.',
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $items = $this->userCreditLimits->all(
            $request->only(['user_id', 'credit_limit_type']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->userCreditLimits->toResource($item))->values(),
            'message' => 'User credit limits retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->userCreditLimits->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->userCreditLimits->toResource($item),
            'message' => 'User credit limit retrieved successfully.',
        ]);
    }

    public function store(StoreUserCreditLimitRequest $request): JsonResponse
    {
        $item = $this->userCreditLimits->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->userCreditLimits->toResource($item),
            'message' => 'User credit limit created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateUserCreditLimitRequest $request): JsonResponse
    {
        $item = $this->userCreditLimits->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->userCreditLimits->toResource($item),
            'message' => 'User credit limit updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->userCreditLimits->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'User credit limit deleted successfully.']);
    }
}
