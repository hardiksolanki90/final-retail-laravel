<?php

namespace App\Repositories;

use App\Models\PaymentTerm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentTermRepository
{
    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function findByUuid(string $uuid, int $organisationId): PaymentTerm
    {
        return PaymentTerm::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): PaymentTerm
    {
        return PaymentTerm::create([
            'organisation_id' => $organisationId,
            'name' => $data['name'] ?? '',
            'payment_code' => $data['paymentCode'] ?? $data['code'] ?? null,
            'number_of_days' => $data['numberOfDays'] ?? 0,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function update(string $uuid, array $data, int $organisationId): PaymentTerm
    {
        $paymentTerm = $this->findByUuid($uuid, $organisationId);

        $paymentTerm->fill([
            'name' => $data['name'] ?? $paymentTerm->name,
            'payment_code' => $data['paymentCode'] ?? $data['code'] ?? $paymentTerm->payment_code,
            'number_of_days' => $data['numberOfDays'] ?? $paymentTerm->number_of_days,
            'status' => array_key_exists('status', $data) ? $data['status'] : $paymentTerm->status,
        ]);
        $paymentTerm->save();

        return $paymentTerm->fresh();
    }

    public function delete(string $uuid, int $organisationId): void
    {
        $this->findByUuid($uuid, $organisationId)->delete();
    }

    public function toResource(PaymentTerm $paymentTerm): array
    {
        return [
            'id' => $paymentTerm->id,
            'uuid' => $paymentTerm->uuid,
            'name' => $paymentTerm->name,
            'paymentCode' => $paymentTerm->payment_code,
            'numberOfDays' => $paymentTerm->number_of_days,
            'status' => (bool) $paymentTerm->status,
            'createdAt' => $paymentTerm->created_at?->toISOString(),
            'updatedAt' => $paymentTerm->updated_at?->toISOString(),
        ];
    }

    public function toSelectOption(PaymentTerm $paymentTerm): array
    {
        return [
            'id' => $paymentTerm->id,
            'uuid' => $paymentTerm->uuid,
            'name' => $paymentTerm->name,
            'numberOfDays' => $paymentTerm->number_of_days,
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = PaymentTerm::where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('payment_code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }
}
