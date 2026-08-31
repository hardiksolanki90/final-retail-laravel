<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'organisation_id', 'order_id', 'customer_id', 'salesman_id', 'reason_id',
    'route_id', 'storage_location_id', 'warehouse_id', 'lob_id', 'delivery_type',
    'delivery_type_source', 'delivery_number', 'invoice_number', 'invoice_route_id',
    'delivery_date', 'change_date', 'delivery_time', 'delivery_due_date', 'delivery_weight',
    'payment_term_id', 'total_qty', 'total_cancel_qty', 'total_gross', 'total_discount_amount',
    'total_net', 'total_vat', 'total_excise', 'grand_total', 'current_stage',
    'current_stage_comment', 'approval_status', 'source', 'status', 'is_approved',
    'is_truck_allocated', 'sync_status', 'picking_status', 'transportation_status',
    'shipment_status', 'invoice_status', 'is_user_updated', 'user_updated', 'module_updated',
])]
class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'change_date' => 'date',
            'delivery_due_date' => 'date',
            'total_qty' => 'decimal:2',
            'total_cancel_qty' => 'decimal:2',
            'total_gross' => 'decimal:2',
            'total_discount_amount' => 'decimal:2',
            'total_net' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_excise' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'status' => 'boolean',
            'is_approved' => 'boolean',
            'is_truck_allocated' => 'boolean',
            'is_user_updated' => 'boolean',
            'source' => 'integer',
            'delivery_type' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Delivery $delivery) {
            $delivery->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryDetail::class);
    }
}
