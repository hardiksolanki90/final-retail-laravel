<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerRepository
{
    protected const RELATIONS = ['route', 'salesman', 'customerType', 'customerCategory', 'customerGroup', 'channel', 'paymentTerm'];

    public function list(array $filters, int $organisationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters, $organisationId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function all(array $filters, int $organisationId): Collection
    {
        return $this->filtered($filters, $organisationId)->orderBy('id')->get();
    }

    public function bySalesman(int $salesmanId, int $organisationId): Collection
    {
        return Customer::with(self::RELATIONS)
            ->where('organisation_id', $organisationId)
            ->where('salesman_id', $salesmanId)
            ->orderBy('id')
            ->get();
    }

    public function findByUuid(string $uuid, int $organisationId): Customer
    {
        return Customer::with(self::RELATIONS)
            ->where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function create(array $data, int $organisationId): Customer
    {
        $customer = Customer::create([
            'organisation_id' => $organisationId,
            'customer_code' => ($data['code'] ?? null) ?: $this->generateCustomerCode($organisationId),
            'erp_code' => $data['erpCode'] ?? null,
            'shop_name' => $data['shopName'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phoneNumber'] ?? null,
            'address' => $data['address'],
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'balance' => $data['balance'] ?? 0,
            'credit_limit' => $data['creditLimit'] ?? 0,
            'credit_days' => $data['creditDays'] ?? 0,
            'trn_no' => $data['trnNo'] ?? null,
            'profile_image' => $data['image'] ?? null,
            'status' => $data['status'] ?? true,
            'route_id' => $data['routeId'] ?? null,
            'salesman_id' => $data['salesmanId'] ?? null,
            'customer_type_id' => $data['customerTypeId'] ?? null,
            'customer_category_id' => $data['customerCategoryId'] ?? null,
            'customer_group_id' => $data['customerGroupId'] ?? null,
            'channel_id' => $data['channelId'] ?? null,
            'payment_term_id' => $data['paymentTermId'] ?? null,
        ]);

        return $customer->load(self::RELATIONS);
    }

    public function update(string $uuid, array $data, int $organisationId): Customer
    {
        $customer = Customer::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $customer->fill([
            'customer_code' => ($data['code'] ?? null) ?: $customer->customer_code,
            'erp_code' => $data['erpCode'] ?? $customer->erp_code,
            'shop_name' => $data['shopName'] ?? $customer->shop_name,
            'firstname' => $data['firstName'] ?? $customer->firstname,
            'lastname' => $data['lastName'] ?? $customer->lastname,
            'email' => $data['email'] ?? $customer->email,
            'phone' => $data['phoneNumber'] ?? $customer->phone,
            'address' => $data['address'] ?? $customer->address,
            'city' => $data['city'] ?? $customer->city,
            'state' => $data['state'] ?? $customer->state,
            'zipcode' => $data['zipcode'] ?? $customer->zipcode,
            'latitude' => $data['latitude'] ?? $customer->latitude,
            'longitude' => $data['longitude'] ?? $customer->longitude,
            'balance' => $data['balance'] ?? $customer->balance,
            'credit_limit' => $data['creditLimit'] ?? $customer->credit_limit,
            'credit_days' => $data['creditDays'] ?? $customer->credit_days,
            'trn_no' => $data['trnNo'] ?? $customer->trn_no,
            'profile_image' => $data['image'] ?? $customer->profile_image,
            'status' => $data['status'] ?? $customer->status,
            'route_id' => array_key_exists('routeId', $data) ? $data['routeId'] : $customer->route_id,
            'salesman_id' => array_key_exists('salesmanId', $data) ? $data['salesmanId'] : $customer->salesman_id,
            'customer_type_id' => array_key_exists('customerTypeId', $data) ? $data['customerTypeId'] : $customer->customer_type_id,
            'customer_category_id' => array_key_exists('customerCategoryId', $data) ? $data['customerCategoryId'] : $customer->customer_category_id,
            'customer_group_id' => array_key_exists('customerGroupId', $data) ? $data['customerGroupId'] : $customer->customer_group_id,
            'channel_id' => array_key_exists('channelId', $data) ? $data['channelId'] : $customer->channel_id,
            'payment_term_id' => array_key_exists('paymentTermId', $data) ? $data['paymentTermId'] : $customer->payment_term_id,
        ]);
        $customer->save();

        return $customer->fresh(self::RELATIONS);
    }

    public function delete(string $uuid, int $organisationId): void
    {
        Customer::where('organisation_id', $organisationId)
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->delete();
    }

    public function bulkAction(array $uuids, string $action, int $organisationId): void
    {
        $query = Customer::where('organisation_id', $organisationId)->whereIn('uuid', $uuids);

        match ($action) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $query->update(['status' => false]),
            'delete' => $query->delete(),
        };
    }

    public function sales(string $uuid, int $organisationId): array
    {
        $this->findByUuid($uuid, $organisationId);

        return [
            'salesData' => [],
            'summary' => [
                'totalOrders' => 0,
                'totalAmount' => 0,
                'avgOrderValue' => 0,
            ],
        ];
    }

    public function toResource(Customer $customer): array
    {
        $fullAddress = collect([$customer->address, $customer->city, $customer->state, $customer->zipcode])
            ->filter()
            ->implode(', ');

        return [
            'id' => $customer->id,
            'uuid' => $customer->uuid,
            'code' => $customer->customer_code,
            'erpCode' => $customer->erp_code,
            'shopName' => $customer->shop_name,
            'firstName' => $customer->firstname,
            'lastName' => $customer->lastname,
            'fullName' => $this->fullName($customer),
            'email' => $customer->email,
            'phoneNumber' => $customer->phone,
            'address' => $customer->address,
            'fullAddress' => $fullAddress,
            'city' => $customer->city,
            'state' => $customer->state,
            'zipcode' => $customer->zipcode,
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
            'balance' => (float) $customer->balance,
            'creditLimit' => (float) $customer->credit_limit,
            'creditDays' => $customer->credit_days,
            'availableCredit' => (float) $customer->credit_limit - (float) $customer->balance,
            'trnNo' => $customer->trn_no,
            'image' => $customer->profile_image,
            'status' => (bool) $customer->status,
            'routeId' => $customer->route_id,
            'salesmanId' => $customer->salesman_id,
            'customerTypeId' => $customer->customer_type_id,
            'customerCategoryId' => $customer->customer_category_id,
            'customerGroupId' => $customer->customer_group_id,
            'channelId' => $customer->channel_id,
            'paymentTermId' => $customer->payment_term_id,
            'createdAt' => $customer->created_at?->toISOString(),
            'updatedAt' => $customer->updated_at?->toISOString(),
            'route' => $customer->relationLoaded('route') && $customer->route
                ? ['id' => $customer->route->id, 'uuid' => $customer->route->uuid, 'name' => $customer->route->route_name]
                : null,
            'salesman' => $customer->relationLoaded('salesman') && $customer->salesman
                ? ['id' => $customer->salesman->id, 'name' => trim($customer->salesman->firstname.' '.$customer->salesman->lastname)]
                : null,
            'customerType' => $customer->relationLoaded('customerType') && $customer->customerType
                ? ['id' => $customer->customerType->id, 'uuid' => $customer->customerType->uuid, 'name' => $customer->customerType->customer_type_name]
                : null,
            'customerCategory' => $customer->relationLoaded('customerCategory') && $customer->customerCategory
                ? ['id' => $customer->customerCategory->id, 'uuid' => $customer->customerCategory->uuid, 'name' => $customer->customerCategory->customer_category_name]
                : null,
            'customerGroup' => $customer->relationLoaded('customerGroup') && $customer->customerGroup
                ? ['id' => $customer->customerGroup->id, 'uuid' => $customer->customerGroup->uuid, 'name' => $customer->customerGroup->group_name]
                : null,
            'channel' => $customer->relationLoaded('channel') && $customer->channel
                ? ['id' => $customer->channel->id, 'uuid' => $customer->channel->uuid, 'name' => $customer->channel->name]
                : null,
            'paymentTerm' => $customer->relationLoaded('paymentTerm') && $customer->paymentTerm
                ? ['id' => $customer->paymentTerm->id, 'uuid' => $customer->paymentTerm->uuid, 'name' => $customer->paymentTerm->name, 'days' => $customer->paymentTerm->number_of_days]
                : null,
        ];
    }

    public function toSelectOption(Customer $customer): array
    {
        return [
            'value' => $customer->uuid,
            'label' => trim($customer->shop_name.' - '.$this->fullName($customer)),
        ];
    }

    protected function filtered(array $filters, int $organisationId): Builder
    {
        $query = Customer::with(self::RELATIONS)->where('organisation_id', $organisationId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        foreach (['route_id', 'salesman_id', 'customer_type_id', 'customer_category_id', 'channel_id'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function generateCustomerCode(int $organisationId): string
    {
        $count = Customer::withTrashed()->where('organisation_id', $organisationId)->count();

        return 'CU-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    protected function fullName(Customer $customer): string
    {
        return trim($customer->firstname.' '.$customer->lastname);
    }
}
