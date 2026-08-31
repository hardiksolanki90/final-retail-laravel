<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Requests\UpdatePaymentTermRequest;
use App\Repositories\PaymentTermRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function __construct(protected PaymentTermRepository $paymentTerms) {}

    public function all(Request $request): JsonResponse
    {
        $items = $this->paymentTerms->all(
            $request->only(['search', 'status']),
            $request->user()->organisation_id,
        );

        return response()->json([
            'data' => $items->map(fn ($item) => $this->paymentTerms->toSelectOption($item))->values(),
            'message' => 'Payment terms retrieved successfully.',
        ]);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $item = $this->paymentTerms->findByUuid($uuid, $request->user()->organisation_id);

        return response()->json([
            'data' => $this->paymentTerms->toResource($item),
            'message' => 'Payment term retrieved successfully.',
        ]);
    }

    public function store(StorePaymentTermRequest $request): JsonResponse
    {
        $item = $this->paymentTerms->create($request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->paymentTerms->toResource($item),
            'message' => 'Payment term created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdatePaymentTermRequest $request): JsonResponse
    {
        $item = $this->paymentTerms->update($uuid, $request->validated(), $request->user()->organisation_id);

        return response()->json([
            'data' => $this->paymentTerms->toResource($item),
            'message' => 'Payment term updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->paymentTerms->delete((string) $request->input('id'), $request->user()->organisation_id);

        return response()->json(['message' => 'Payment term deleted successfully.']);
    }
}
