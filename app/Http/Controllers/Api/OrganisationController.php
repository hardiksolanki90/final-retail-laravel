<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Repositories\OrganisationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function __construct(protected OrganisationRepository $organisations) {}

    public function current(Request $request): JsonResponse
    {
        $organisation = $this->organisations->current($request->user());

        return response()->json([
            'data' => $organisation,
            'message' => 'Organisation retrieved successfully.',
        ]);
    }

    public function update(UpdateOrganisationRequest $request): JsonResponse
    {
        $organisation = $this->organisations->update($request->user(), $request->validated());

        return response()->json([
            'data' => $organisation,
            'message' => 'Organisation updated successfully.',
        ]);
    }
}
