<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'organisation_id',
    'customer_code',
    'erp_code',
    'shop_name',
    'firstname',
    'lastname',
    'email',
    'phone',
    'address',
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
    use HasFactory, SoftDeletes;

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

    // Assigned salesman only — customers never log in, this is not a login link.
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
}
