<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — debit_notes.
     */
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('lob_id')->nullable();
            $table->string('reason', 191)->nullable();
            $table->string('debit_note_number', 191);
            $table->date('debit_note_date');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->decimal('total_qty', 18, 2)->default(0);
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_discount_amount', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0)->comment('total_gross - total_discount_amount');
            $table->decimal('total_vat', 18, 2)->default(0);
            $table->decimal('total_excise', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0)->comment('total_net + total_vat + total_excise');
            $table->decimal('pending_credit', 18, 3)->default(0);
            $table->decimal('pdc_amount', 18, 3)->default(0);
            $table->text('debit_note_comment')->nullable();
            $table->integer('source')->comment('1:Mobile, 2:Backend, 3:Frontend');
            $table->boolean('status')->default(true);
            $table->boolean('is_debit_note')->default(true);
            $table->date('supplier_recipt_date')->nullable();
            $table->string('supplier_recipt_number', 191)->nullable();
            $table->enum('debit_note_type', [
                'debit_note', 'listing_fees', 'shelf_rent', 'rebate_discount',
            ])->default('debit_note');
            $table->enum('approval_status', ['Created', 'Updated', 'Deleted'])->default('Created');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'debit_note_number']);
            $table->index(['organisation_id', 'debit_note_date']);
            $table->index(['organisation_id', 'approval_status']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
