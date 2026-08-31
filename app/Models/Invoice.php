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
    'uuid', 'organisation_id', 'customer_id', 'depot_id', 'order_id', 'order_type_id',
    'delivery_id', 'salesman_id', 'reason_id', 'trip_id', 'van_id', 'route_id',
    'storage_location_id', 'warehouse_id', 'lob_id', 'invoice_type', 'invoice_number',
    'invoice_date', 'invoice_due_date', 'payment_term_id', 'total_qty', 'total_cancel_qty',
    'total_gross', 'total_discount_amount', 'total_net', 'total_vat', 'total_excise',
    'grand_total', 'rounding_off_amount', 'pending_credit', 'pdc_amount', 'current_stage',
    'current_stage_comment', 'approval_status', 'payment_received', 'is_exchange',
    'exchange_number', 'is_premium_invoice', 'customer_lpo', 'source', 'status',
    'is_submitted', 'oddo_post_id', 'odoo_failed_response', 'mobile_created_at',
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'invoice_due_date' => 'date',
            'mobile_created_at' => 'datetime',
            'total_qty' => 'decimal:2',
            'total_cancel_qty' => 'decimal:2',
            'total_gross' => 'decimal:2',
            'total_discount_amount' => 'decimal:2',
            'total_net' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_excise' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'rounding_off_amount' => 'decimal:2',
            'pending_credit' => 'decimal:2',
            'pdc_amount' => 'decimal:2',
            'payment_received' => 'boolean',
            'is_exchange' => 'boolean',
            'is_premium_invoice' => 'boolean',
            'status' => 'boolean',
            'is_submitted' => 'boolean',
            'source' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->uuid ??= (string) Str::uuid();
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
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
        return $this->hasMany(InvoiceDetail::class);
    }
}
