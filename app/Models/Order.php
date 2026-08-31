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
    'uuid', 'organisation_id', 'customer_id', 'depot_id', 'order_type_id', 'salesman_id',
    'route_id', 'storage_location_id', 'warehouse_id', 'lob_id', 'erp_number', 'customer_lop',
    'order_number', 'order_date', 'due_date', 'delivery_date', 'change_date', 'hold_reason',
    'reason_id', 'payment_term_id', 'total_qty', 'total_cancel_qty', 'total_gross',
    'total_discount_amount', 'total_net', 'total_vat', 'total_excise', 'grand_total',
    'any_comment', 'current_stage', 'current_stage_comment', 'approval_status', 'sign_image',
    'source', 'status', 'is_approved', 'sync_status', 'order_created_user_id', 'order_status',
    'order_generate_picking', 'invoice_id', 'picking_status', 'transportation_status',
    'shipment_status', 'invoice_status', 'is_user_updated', 'user_updated', 'module_updated',
    'is_presale_order',
])]
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'change_date' => 'date',
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
            'order_generate_picking' => 'boolean',
            'is_user_updated' => 'boolean',
            'is_presale_order' => 'boolean',
            'source' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
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

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
