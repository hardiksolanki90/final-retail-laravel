<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors debit_notes — return-flow counterpart. Trimmed from the nfpc
     * reference (SAP/JDE, workflow objects, approval images, notes sub-table
     * dropped — not part of this rebuild's scope).
     */
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->string('credit_note_number', 191);
            $table->date('credit_note_date');
            $table->string('reason', 191)->nullable();
            $table->decimal('total_qty', 18, 2)->default(0);
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_discount_amount', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0)->comment('total_gross - total_discount_amount');
            $table->decimal('total_vat', 18, 2)->default(0);
            $table->decimal('total_excise', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0)->comment('total_net + total_vat + total_excise');
            $table->decimal('pending_credit', 18, 3)->default(0);
            $table->text('credit_note_comment')->nullable();
            $table->integer('source')->comment('1:Mobile, 2:Backend, 3:Frontend');
            $table->boolean('status')->default(true);
            $table->enum('approval_status', ['Created', 'Updated', 'Deleted'])->default('Created');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'credit_note_number']);
            $table->index(['organisation_id', 'credit_note_date']);
            $table->index(['organisation_id', 'approval_status']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
