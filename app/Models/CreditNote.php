<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Organisationid;
use App\Traits\Filterable;

#[Fillable([
    'uuid', 'organisation_id', 'invoice_id', 'customer_id', 'salesman_id', 'route_id',
    'payment_term_id', 'credit_note_number', 'credit_note_date', 'reason',
    'total_qty', 'total_gross', 'total_discount_amount', 'total_net', 'total_vat',
    'total_excise', 'grand_total', 'pending_credit', 'credit_note_comment',
    'source', 'status', 'approval_status',
])]
class CreditNote extends Model
{
    use HasFactory, SoftDeletes, Organisationid, Filterable;

    protected array $searchable = ['credit_note_number', 'reason'];
    protected array $filterable = ['customer_id', 'status'];
    protected string $dateRangeColumn = 'credit_note_date';

    protected function casts(): array
    {
        return [
            'credit_note_date' => 'date',
            'total_qty' => 'decimal:2',
            'total_gross' => 'decimal:2',
            'total_discount_amount' => 'decimal:2',
            'total_net' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_excise' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'pending_credit' => 'decimal:3',
            'status' => 'boolean',
            'source' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CreditNote $creditNote) {
            $creditNote->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(CreditNoteDetail::class);
    }
}
