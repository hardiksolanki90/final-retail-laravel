<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\VanCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanCategoryController extends Controller
{
    public function __construct(protected VanCategoryRepository $vanCategories) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->vanCategories->all(
            $request->only(['status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->vanCategories->toSelectOption($item))->values(),
            'message' => 'Van categories retrieved successfully.',
        ]);
    }
}
