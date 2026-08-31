<?php

namespace App\Repositories;

use App\Models\BankInformation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BankInformationRepository
{
    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)->orderByDesc('id')->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): BankInformation
    {
        return BankInformation::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): BankInformation
    {
        return BankInformation::create([
            'organisation_id' => $organisationId,
            'bank_code' => $data['bankCode'],
            'bank_name' => $data['bankName'],
            'bank_address' => $data['bankAddress'],
            'account_number' => $data['accountNumber'],
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): BankInformation
    {
        $bankInformation = $this->findByUuid($uuid, $organisationId);

        $bankInformation->fill([
            'bank_code' => $data['bankCode'] ?? $bankInformation->bank_code,
            'bank_name' => $data['bankName'] ?? $bankInformation->bank_name,
            'bank_address' => $data['bankAddress'] ?? $bankInformation->bank_address,
            'account_number' => $data['accountNumber'] ?? $bankInformation->account_number,
            'status' => array_key_exists('status', $data) ? $data['status'] : $bankInformation->status,
        ]);
        $bankInformation->save();

        return $bankInformation->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(BankInformation $bankInformation): array
    {
        return [
            'id' => $bankInformation->id,
            'uuid' => $bankInformation->uuid,
            'bankCode' => $bankInformation->bank_code,
            'bankName' => $bankInformation->bank_name,
            'bankAddress' => $bankInformation->bank_address,
            'accountNumber' => $bankInformation->account_number,
            'status' => (bool) $bankInformation->status,
            'createdAt' => $bankInformation->created_at?->toISOString(),
            'updatedAt' => $bankInformation->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(BankInformation $bankInformation): array
    {
        return [
            'id' => $bankInformation->id,
            'uuid' => $bankInformation->uuid,
            'bankName' => $bankInformation->bank_name,
            'bankCode' => $bankInformation->bank_code,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = BankInformation::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('bank_code', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
