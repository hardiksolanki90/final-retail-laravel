<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Organisationid;
use App\Traits\Filterable;

#[Fillable([
    'uuid',
    'organisation_id',
    'user_id',
    'customer_code',
    'shop_name',
    'firstname',
    'lastname',
    'email',
    'phone',
    'address',
    'customer_office_address',
    'customer_office_city',
    'customer_office_state',
    'customer_office_zipcode',
    'customer_office_phone',
    'customer_office_lat',
    'customer_office_lang',
    'customer_home_address',
    'customer_home_lat',
    'customer_home_lang',
    'sales_organisation_id',
    'country_id',
    'region_id',
    'merchandiser_id',
    'ship_to_party_id',
    'sold_to_party_id',
    'payer_id',
    'bill_to_party_id',
    'city',
    'state',
    'zipcode',
    'latitude',
    'longitude',
    'balance',
    'credit_limit',
    'credit_days',
    'trn_no',
    'profile_image',
    'status',
    'route_id',
    'salesman_id',
    'customer_type_id',
    'customer_category_id',
    'customer_group_id',
    'channel_id',
    'payment_term_id',
])]
class Customer extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['customer_code', 'shop_name', 'firstname', 'lastname', 'email', 'phone'];
    protected array $filterable = ['route_id', 'salesman_id', 'customer_type_id', 'customer_category_id', 'channel_id', 'status'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'credit_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            $customer->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    // Optional portal login — null unless the customer has one enabled.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Assigned salesman only — not the customer's own login link (see user()).
    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function customerCategory(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function salesOrganisation(): BelongsTo
    {
        return $this->belongsTo(SalesOrganisation::class, 'sales_organisation_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function merchandiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchandiser_id');
    }

    public function shipToParty(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'ship_to_party_id');
    }

    public function soldToParty(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'sold_to_party_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'payer_id');
    }

    public function billToParty(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'bill_to_party_id');
    }
}
