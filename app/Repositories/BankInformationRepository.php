<?php

namespace App\Repositories;

use App\Http\Requests\StoreBankInformationRequest;
use App\Http\Requests\UpdateBankInformationRequest;
use App\Http\Resources\BankInformationList;
use App\Http\Resources\BankInformationView;
use App\Models\BankInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankInformationRepository
{
    public function list(Request $request): JsonResponse
    {
        $paginated = BankInformation::filter($request->only(['search', 'status']))
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (BankInformation $item) => (new BankInformationList($item))->resolve());

        return response()->json(paginated($paginated, 'bankInformation'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = BankInformation::filter($request->only(['search', 'status']))->orderBy('id')->get();

        return response()->json([
            'data' => $items->map(fn (BankInformation $item) => $this->toSelectOption($item))->values(),
            'message' => 'Bank information retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $item = $this->findByUuid($uuid);

        return response()->json([
            'data' => (new BankInformationView($item))->resolve(),
            'message' => 'Bank information retrieved successfully.',
        ]);
    }

    public function store(StoreBankInformationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = BankInformation::create([
            'bank_code' => $data['bankCode'],
            'bank_name' => $data['bankName'],
            'bank_address' => $data['bankAddress'],
            'account_number' => $data['accountNumber'],
            'status' => $data['status'] ?? true,
            'iban' => $data['iban'] ?? null,
            'swift_code' => $data['swiftCode'] ?? null,
            'ifsc_code' => $data['ifscCode'] ?? null,
            'routing_number' => $data['routingNumber'] ?? null,
            'sort_code' => $data['sortCode'] ?? null,
            'branch_name' => $data['branchName'] ?? null,
        ]);

        return response()->json([
            'data' => (new BankInformationView($item))->resolve(),
            'message' => 'Bank information created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateBankInformationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $bankInformation = $this->findByUuid($uuid);

        $bankInformation->fill([
            'bank_code' => $data['bankCode'] ?? $bankInformation->bank_code,
            'bank_name' => $data['bankName'] ?? $bankInformation->bank_name,
            'bank_address' => $data['bankAddress'] ?? $bankInformation->bank_address,
            'account_number' => $data['accountNumber'] ?? $bankInformation->account_number,
            'status' => array_key_exists('status', $data) ? $data['status'] : $bankInformation->status,
            'iban' => $data['iban'] ?? $bankInformation->iban,
            'swift_code' => $data['swiftCode'] ?? $bankInformation->swift_code,
            'ifsc_code' => $data['ifscCode'] ?? $bankInformation->ifsc_code,
            'routing_number' => $data['routingNumber'] ?? $bankInformation->routing_number,
            'sort_code' => $data['sortCode'] ?? $bankInformation->sort_code,
            'branch_name' => $data['branchName'] ?? $bankInformation->branch_name,
        ]);
        $bankInformation->save();

        return response()->json([
            'data' => (new BankInformationView($bankInformation->fresh()))->resolve(),
            'message' => 'Bank information updated successfully.',
        ]);
    }

    public function destroyByUuid(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid)->delete();

        return response()->json(['message' => 'Bank information deleted successfully.']);
    }

    protected function findByUuid(string $uuid): BankInformation
    {
        return BankInformation::where('uuid', $uuid)->firstOrFail();
    }

    protected function toSelectOption(BankInformation $bankInformation): array
    {
        return [
            'id' => $bankInformation->id,
            'uuid' => $bankInformation->uuid,
            'bankName' => $bankInformation->bank_name,
            'bankCode' => $bankInformation->bank_code,
        ];
    }
}

