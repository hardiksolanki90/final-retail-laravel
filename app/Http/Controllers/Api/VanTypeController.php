<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\VanTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VanTypeController extends Controller
{
    public function __construct(protected VanTypeRepository $vanTypes) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->vanTypes->all(
            $request->only(['status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->vanTypes->toSelectOption($item))->values(),
            'message' => 'Van types retrieved successfully.',
        ]);
    }
}
