<?php

namespace App\Repositories;

use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Requests\UpdatePaymentTermRequest;
use App\Http\Resources\PaymentTermList;
use App\Http\Resources\PaymentTermView;
use App\Models\PaymentTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTermRepository
{
    public function all(Request $request): JsonResponse
    {
        $paginated = PaymentTerm::filter($request->only(['search', 'status']))
            ->orderBy('id')
            ->paginate((int) $request->input('per_page', 50))
            ->through(fn (PaymentTerm $item) => $this->toSelectOption($item));

        return response()->json(paginated($paginated, 'paymentTerms'), 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new PaymentTermView($item))->resolve(),
            'message' => 'Payment term retrieved successfully.',
        ]);
    }

    public function store(StorePaymentTermRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = PaymentTerm::create([
            'name' => $data['name'] ?? '',
            'payment_code' => $data['paymentCode'] ?? $data['code'] ?? null,
            'number_of_days' => $data['numberOfDays'] ?? 0,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json([
            'data' => (new PaymentTermView($item))->resolve(),
            'message' => 'Payment term created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdatePaymentTermRequest $request): JsonResponse
    {
        $data = $request->validated();
        $paymentTerm = $this->findByUuid($uuid);

        $paymentTerm->fill([
            'name' => $data['name'] ?? $paymentTerm->name,
            'payment_code' => $data['paymentCode'] ?? $data['code'] ?? $paymentTerm->payment_code,
            'number_of_days' => $data['numberOfDays'] ?? $paymentTerm->number_of_days,
            'status' => array_key_exists('status', $data) ? $data['status'] : $paymentTerm->status,
        ]);
        $paymentTerm->save();

        return response()->json([
            'data' => (new PaymentTermView($paymentTerm->fresh()))->resolve(),
            'message' => 'Payment term updated successfully.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'string']]);

        $this->findByUuid((string) $request->input('id'))->delete();

        return response()->json(['message' => 'Payment term deleted successfully.']);
    }

    protected function findByUuid(string $uuid): PaymentTerm
    {
        return PaymentTerm::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(PaymentTerm $paymentTerm): array
    {
        return [
            'id' => $paymentTerm->id,
            'uuid' => $paymentTerm->uuid,
            'name' => $paymentTerm->name,
            'numberOfDays' => $paymentTerm->number_of_days,
        ];
    }
}

