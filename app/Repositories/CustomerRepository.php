<?php

namespace App\Repositories;

use App\Http\Requests\BulkCustomerActionRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerRepository
{
    protected const RELATIONS = ['route', 'salesman', 'customerType', 'customerCategory', 'customerGroup', 'channel', 'paymentTerm', 'user', 'salesOrganisation'];

    public function list(Request $request): JsonResponse
    {
        $paginated = Customer::filter($request->only(['search', 'route_id', 'salesman_id', 'customer_type_id', 'customer_category_id', 'channel_id', 'status']))
            ->with(self::RELATIONS)
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->through(fn (Customer $item) => $this->toResource($item));

        return response()->json(paginated($paginated, 'customers'), 200);
    }

    public function all(Request $request): JsonResponse
    {
        $items = Customer::filter($request->only(['route_id', 'salesman_id', 'status']))
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Customer $customer) => $this->toSelectOption($customer))->values(),
            'message' => 'Customers retrieved successfully.',
        ]);
    }

    public function bySalesman(int $salesmanId): JsonResponse
    {
        $items = Customer::with(self::RELATIONS)
            ->where('salesman_id', $salesmanId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $items->map(fn (Customer $customer) => $this->toResource($customer))->values(),
            'message' => 'Customers retrieved successfully.',
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $customer = $this->findByUuid($uuid);

        return response()->json([
            'data' => $this->toResource($customer),
            'message' => 'Customer retrieved successfully.',
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->create($request->validated());

        return response()->json([
            'data' => $this->toResource($customer),
            'message' => 'Customer created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCustomerRequest $request): JsonResponse
    {
        $customer = $this->performUpdate($uuid, $request->validated());

        return response()->json([
            'data' => $this->toResource($customer),
            'message' => 'Customer updated successfully.',
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        DB::transaction(function () use ($uuid) {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();

            $customer->user?->delete();
            $customer->delete();
        });

        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    public function bulkAction(BulkCustomerActionRequest $request): JsonResponse
    {
        $query = Customer::whereIn('uuid', $request->validated('uuids'));

        match ($request->validated('action')) {
            'activate' => $query->update(['status' => true]),
            'deactivate' => $this->cascadeStatusToLinkedUsers($query, false),
            'delete' => $this->cascadeDeleteWithLinkedUsers($query),
        };

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    public function sales(string $uuid): JsonResponse
    {
        $this->findByUuid($uuid);

        return response()->json([
            'data' => [
                'salesData' => [],
                'summary' => [
                    'totalOrders' => 0,
                    'totalAmount' => 0,
                    'avgOrderValue' => 0,
                ],
            ],
            'message' => 'Customer sales retrieved successfully.',
        ]);
    }

    protected function findByUuid(string $uuid): Customer
    {
        return Customer::with(self::RELATIONS)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    protected function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            // organisation_id is still needed explicitly here because the
            // linked login lives on the users table, which is intentionally
            // excluded from automatic organisation scoping.
            $organisationId = Auth::user()->organisation_id;

            $userId = ! empty($data['enableLogin'])
                ? $this->createLoginAccount($data, $organisationId)->id
                : null;

            $isSame = fn ($val) => $val === 'same_as_code' || $val === 'self';

            $customer = Customer::create([
                'user_id' => $userId,
                'customer_code' => ($data['code'] ?? null) ?: $this->generateCustomerCode(),
                'shop_name' => $data['shopName'],
                'firstname' => $data['firstName'],
                'lastname' => $data['lastName'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phoneNumber'] ?? null,
                'customer_office_address' => $data['customerOfficeAddress'] ?? null,
                'customer_office_city' => $data['customerOfficeCity'] ?? null,
                'customer_office_state' => $data['customerOfficeState'] ?? null,
                'customer_office_zipcode' => $data['customerOfficeZipcode'] ?? null,
                'customer_office_phone' => $data['customerOfficePhone'] ?? null,
                'customer_office_lat' => $data['customerOfficeLat'] ?? null,
                'customer_office_lang' => $data['customerOfficeLang'] ?? null,
                'customer_home_address' => $data['customerHomeAddress'] ?? null,
                'customer_home_lat' => $data['customerHomeLat'] ?? null,
                'customer_home_lang' => $data['customerHomeLang'] ?? null,
                'sales_organisation_id' => $data['salesOrganisationId'] ?? null,
                'country_id' => $data['countryId'] ?? null,
                'region_id' => $data['regionId'] ?? null,
                'merchandiser_id' => $data['merchandiserId'] ?? null,
                'ship_to_party_id' => $isSame($data['shipToPartyId'] ?? null) ? null : ($data['shipToPartyId'] ?? null),
                'sold_to_party_id' => $isSame($data['soldToPartyId'] ?? null) ? null : ($data['soldToPartyId'] ?? null),
                'payer_id' => $isSame($data['payerId'] ?? null) ? null : ($data['payerId'] ?? null),
                'bill_to_party_id' => $isSame($data['billToPartyId'] ?? null) ? null : ($data['billToPartyId'] ?? null),
                'address' => $data['customerOfficeAddress'] ?? null, // Fallback for original column
                'city' => $data['customerOfficeCity'] ?? null,
                'state' => $data['customerOfficeState'] ?? null,
                'zipcode' => $data['customerOfficeZipcode'] ?? null,
                'latitude' => $data['customerOfficeLat'] ?? null,
                'longitude' => $data['customerOfficeLang'] ?? null,
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

            $selfPartners = [];
            if ($isSame($data['shipToPartyId'] ?? null)) {
                $selfPartners['ship_to_party_id'] = $customer->id;
            }
            if ($isSame($data['soldToPartyId'] ?? null)) {
                $selfPartners['sold_to_party_id'] = $customer->id;
            }
            if ($isSame($data['payerId'] ?? null)) {
                $selfPartners['payer_id'] = $customer->id;
            }
            if ($isSame($data['billToPartyId'] ?? null)) {
                $selfPartners['bill_to_party_id'] = $customer->id;
            }
            if (! empty($selfPartners)) {
                $customer->update($selfPartners);
            }

            return $customer->load(self::RELATIONS);
        });
    }

    protected function performUpdate(string $uuid, array $data): Customer
    {
        return DB::transaction(function () use ($uuid, $data) {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();

            // organisation_id is still needed explicitly here because the
            // linked login lives on the users table, which is intentionally
            // excluded from automatic organisation scoping.
            $this->syncLoginAccount($customer, $data, Auth::user()->organisation_id);

            $customer->fill([
                'customer_code' => ($data['code'] ?? null) ?: $customer->customer_code,
                'shop_name' => $data['shopName'] ?? $customer->shop_name,
                'firstname' => $data['firstName'] ?? $customer->firstname,
                'lastname' => $data['lastName'] ?? $customer->lastname,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phoneNumber'] ?? $customer->phone,
                'customer_office_address' => $data['customerOfficeAddress'] ?? $customer->customer_office_address,
                'customer_office_city' => $data['customerOfficeCity'] ?? $customer->customer_office_city,
                'customer_office_state' => $data['customerOfficeState'] ?? $customer->customer_office_state,
                'customer_office_zipcode' => $data['customerOfficeZipcode'] ?? $customer->customer_office_zipcode,
                'customer_office_phone' => $data['customerOfficePhone'] ?? $customer->customer_office_phone,
                'customer_office_lat' => $data['customerOfficeLat'] ?? $customer->customer_office_lat,
                'customer_office_lang' => $data['customerOfficeLang'] ?? $customer->customer_office_lang,
                'customer_home_address' => $data['customerHomeAddress'] ?? $customer->customer_home_address,
                'customer_home_lat' => $data['customerHomeLat'] ?? $customer->customer_home_lat,
                'customer_home_lang' => $data['customerHomeLang'] ?? $customer->customer_home_lang,
                'sales_organisation_id' => $data['salesOrganisationId'] ?? $customer->sales_organisation_id,
                'country_id' => $data['countryId'] ?? $customer->country_id,
                'region_id' => $data['regionId'] ?? $customer->region_id,
                'merchandiser_id' => $data['merchandiserId'] ?? $customer->merchandiser_id,
                'ship_to_party_id' => (($data['shipToPartyId'] ?? null) === 'same_as_code' || ($data['shipToPartyId'] ?? null) === 'self') ? $customer->id : ($data['shipToPartyId'] ?? $customer->ship_to_party_id),
                'sold_to_party_id' => (($data['soldToPartyId'] ?? null) === 'same_as_code' || ($data['soldToPartyId'] ?? null) === 'self') ? $customer->id : ($data['soldToPartyId'] ?? $customer->sold_to_party_id),
                'payer_id' => (($data['payerId'] ?? null) === 'same_as_code' || ($data['payerId'] ?? null) === 'self') ? $customer->id : ($data['payerId'] ?? $customer->payer_id),
                'bill_to_party_id' => (($data['billToPartyId'] ?? null) === 'same_as_code' || ($data['billToPartyId'] ?? null) === 'self') ? $customer->id : ($data['billToPartyId'] ?? $customer->bill_to_party_id),
                'address' => $data['customerOfficeAddress'] ?? $customer->address,
                'city' => $data['customerOfficeCity'] ?? $customer->city,
                'state' => $data['customerOfficeState'] ?? $customer->state,
                'zipcode' => $data['customerOfficeZipcode'] ?? $customer->zipcode,
                'latitude' => $data['customerOfficeLat'] ?? $customer->latitude,
                'longitude' => $data['customerOfficeLang'] ?? $customer->longitude,
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

            // Deactivating the customer also revokes any portal login — a
            // deactivated business relationship can't stay signed in.
            // Reactivating does NOT auto-restore it; that's a separate,
            // explicit toggle back on.
            if (array_key_exists('status', $data) && ! $data['status'] && $customer->user_id) {
                $customer->user()->update(['status' => false]);
            }

            return $customer->fresh(self::RELATIONS);
        });
    }

    /**
     * Creates or updates the customer's optional linked login as the login
     * fields on $data dictate, ahead of the caller filling in business
     * fields on the same $customer instance. Mirrors SalesmanRepository's
     * create/update split, adapted for an optional (not mandatory) link.
     */
    protected function syncLoginAccount(Customer $customer, array $data, int $organisationId): void
    {
        $wantsLogin = ! empty($data['enableLogin']);

        if ($wantsLogin && ! $customer->user_id) {
            $customer->user_id = $this->createLoginAccount($data, $organisationId)->id;

            return;
        }

        if ($wantsLogin && $customer->user_id) {
            $user = $customer->user;

            if ($user && ($data['email'] ?? null) && $data['email'] !== $user->email) {
                $emailTaken = User::where('email', $data['email'])->where('id', '!=', $user->id)->exists();

                if ($emailTaken) {
                    throw ValidationException::withMessages(['email' => ['The email has already been taken.']]);
                }
            }

            $user?->fill([
                'firstname' => $data['firstName'] ?? $user->firstname,
                'lastname' => $data['lastName'] ?? $user->lastname,
                'email' => $data['email'] ?? $user->email,
                'mobile' => $data['phoneNumber'] ?? $user->mobile,
            ]);

            if (! empty($data['password'])) {
                $user?->forceFill(['password' => Hash::make($data['password'])]);
            }

            $user?->save();

            return;
        }

        if (! $wantsLogin && $customer->user_id) {
            $customer->user?->delete();
            $customer->user_id = null;
        }
    }

    protected function createLoginAccount(array $data, int $organisationId): User
    {
        // User is intentionally excluded from automatic organisation
        // scoping, so organisation_id must still be set explicitly here.
        return User::create([
            'uuid' => (string) Str::uuid(),
            'organisation_id' => $organisationId,
            'usertype' => 2,
            'role_id' => Role::where('code', 'customer')->where('organisation_id', $organisationId)->value('id'),
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'] ?? '',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => $data['phoneNumber'] ?? null,
            'login_type' => 'system',
            'status' => true,
            'is_approved_by_admin' => true,
        ]);
    }

    protected function cascadeStatusToLinkedUsers(Builder $query, bool $status): void
    {
        $userIds = (clone $query)->whereNotNull('user_id')->pluck('user_id');
        $query->update(['status' => $status]);

        if ($userIds->isNotEmpty()) {
            User::whereIn('id', $userIds)->update(['status' => $status]);
        }
    }

    protected function cascadeDeleteWithLinkedUsers(Builder $query): void
    {
        $userIds = (clone $query)->whereNotNull('user_id')->pluck('user_id');
        $query->delete();

        if ($userIds->isNotEmpty()) {
            User::whereIn('id', $userIds)->delete();
        }
    }

    public function toResource(Customer $customer): array
    {
        $fullAddress = collect([$customer->customer_office_address ?? $customer->address, $customer->customer_office_city ?? $customer->city, $customer->customer_office_state ?? $customer->state, $customer->customer_office_zipcode ?? $customer->zipcode])
            ->filter()
            ->implode(', ');

        return [
            'id' => $customer->id,
            'uuid' => $customer->uuid,
            'code' => $customer->customer_code,
            'shopName' => $customer->shop_name,
            'firstName' => $customer->firstname,
            'lastName' => $customer->lastname,
            'fullName' => $this->fullName($customer),
            'email' => $customer->email,
            'phoneNumber' => $customer->phone,
            'customerOfficeAddress' => $customer->customer_office_address ?? $customer->address,
            'customerOfficeCity' => $customer->customer_office_city ?? $customer->city,
            'customerOfficeState' => $customer->customer_office_state ?? $customer->state,
            'customerOfficeZipcode' => $customer->customer_office_zipcode ?? $customer->zipcode,
            'customerOfficePhone' => $customer->customer_office_phone ?? $customer->phone,
            'customerOfficeLat' => $customer->customer_office_lat ?? $customer->latitude,
            'customerOfficeLang' => $customer->customer_office_lang ?? $customer->longitude,
            'customerHomeAddress' => $customer->customer_home_address,
            'customerHomeLat' => $customer->customer_home_lat,
            'customerHomeLang' => $customer->customer_home_lang,
            'fullAddress' => $fullAddress,
            'balance' => (float) $customer->balance,
            'creditLimit' => (float) $customer->credit_limit,
            'creditDays' => $customer->credit_days,
            'availableCredit' => (float) $customer->credit_limit - (float) $customer->balance,
            'trnNo' => $customer->trn_no,
            'image' => $customer->profile_image,
            'status' => (bool) $customer->status,
            'hasLoginAccess' => (bool) $customer->user_id,
            'user' => $customer->relationLoaded('user') && $customer->user
                ? [
                    'id' => $customer->user->id,
                    'uuid' => $customer->user->uuid,
                    'email' => $customer->user->email,
                    'status' => (bool) $customer->user->status,
                ]
                : null,
            'routeId' => $customer->route_id,
            'salesmanId' => $customer->salesman_id,
            'customerTypeId' => $customer->customer_type_id,
            'customerCategoryId' => $customer->customer_category_id,
            'customerGroupId' => $customer->customer_group_id,
            'channelId' => $customer->channel_id,
            'paymentTermId' => $customer->payment_term_id,
            'salesOrganisationId' => $customer->sales_organisation_id,
            'countryId' => $customer->country_id,
            'regionId' => $customer->region_id,
            'merchandiserId' => $customer->merchandiser_id,
            'shipToPartyId' => $customer->ship_to_party_id,
            'soldToPartyId' => $customer->sold_to_party_id,
            'payerId' => $customer->payer_id,
            'billToPartyId' => $customer->bill_to_party_id,
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
            'salesOrganisation' => $customer->relationLoaded('salesOrganisation') && $customer->salesOrganisation
                ? ['id' => $customer->salesOrganisation->id, 'uuid' => $customer->salesOrganisation->uuid, 'name' => $customer->salesOrganisation->name]
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

    protected function generateCustomerCode(): string
    {
        $count = Customer::withTrashed()->count();

        return 'CU-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    protected function fullName(Customer $customer): string
    {
        return trim($customer->firstname.' '.$customer->lastname);
    }
}
