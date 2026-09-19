<?php

namespace App\Repositories;

use App\Http\Requests\StoreUserCreditLimitRequest;
use App\Http\Requests\UpdateUserCreditLimitRequest;
use App\Models\UserCreditLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCreditLimitRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = UserCreditLimit::filter($request->only(['user_id', 'credit_limit_type']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (UserCreditLimit $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'userCreditLimits'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = UserCreditLimit::filter($request->only(['user_id', 'credit_limit_type']))
            ->with('user')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (UserCreditLimit $item) => $this->toResource($item))->values(),
            'message' => 'User credit limits retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'User credit limit retrieved successfully.',
        ]);
    }

    public function store(StoreUserCreditLimitRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = UserCreditLimit::create([
            'user_id' => $data['userId'],
            'credit_limit_type' => $data['creditLimitType'],
        ]);

        return response()->json([
            'data' => $this->toResource($item),
            'message' => 'User credit limit created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateUserCreditLimitRequest $request): JsonResponse
    {
        $data = $request->validated();
        $item = $this->findByUuid($uuid);

        $item->fill([
            'user_id' => $data['userId'] ?? $item->user_id,
            'credit_limit_type' => $data['creditLimitType'] ?? $item->credit_limit_type,
        ]);
        $item->save();

        return response()->json([
            'data' => $this->toResource($item->fresh()),
            'message' => 'User credit limit updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'User credit limit deleted successfully.']);
    }

    protected function findByUuid(string $uuid): UserCreditLimit
    {
        return UserCreditLimit::where('uuid', $uuid)->firstOrFail();
    }

    protected function toResource(UserCreditLimit $userCreditLimit): array
    {
        $resource = [
            'id' => $userCreditLimit->id,
            'uuid' => $userCreditLimit->uuid,
            'userId' => $userCreditLimit->user_id,
            'creditLimitType' => $userCreditLimit->credit_limit_type,
        ];

        if ($userCreditLimit->relationLoaded('user') && $userCreditLimit->user) {
            $resource['user'] = [
                'id' => $userCreditLimit->user->id,
                'name' => trim($userCreditLimit->user->firstname.' '.$userCreditLimit->user->lastname),
            ];
        }

        return $resource;
    }
}
