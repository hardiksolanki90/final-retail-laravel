<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — invoices.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('depot_id')->nullable()->constrained('depots');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->unsignedBigInteger('order_type_id');
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries');
            $table->foreignId('salesman_id')->nullable()->constrained('users');
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->foreignId('van_id')->nullable()->constrained('vans');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->unsignedBigInteger('storage_location_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->unsignedBigInteger('lob_id')->nullable();
            $table->enum('invoice_type', ['1', '2'])->default('1')
                ->comment('1.Invoicing, 2.OTC-Order to Cash');
            $table->string('invoice_number', 191);
            $table->date('invoice_date');
            $table->date('invoice_due_date');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->decimal('total_qty', 18, 2)->default(0);
            $table->decimal('total_cancel_qty', 8, 2)->default(0);
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_discount_amount', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0)->comment('total_gross - total_discount_amount');
            $table->decimal('total_vat', 18, 2)->default(0);
            $table->decimal('total_excise', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0)->comment('total_net + total_vat + total_excise');
            $table->decimal('rounding_off_amount', 18, 2)->default(0);
            $table->decimal('pending_credit', 18, 2)->default(0);
            $table->decimal('pdc_amount', 18, 2)->default(0);
            $table->enum('current_stage', [
                'Pending', 'Approved', 'Rejected', 'In-Process', 'Completed',
            ])->default('Pending');
            $table->text('current_stage_comment')->nullable();
            $table->enum('approval_status', [
                'Deleted', 'Created', 'Updated', 'In-Process', 'Completed',
            ])->default('Created');
            $table->boolean('payment_received')->default(false);
            $table->boolean('is_exchange')->default(false);
            $table->string('exchange_number', 50)->nullable();
            $table->boolean('is_premium_invoice')->nullable();
            $table->string('customer_lpo', 100)->nullable();
            $table->integer('source')->comment('1:Mobile, 2:Backend, 3:Frontend');
            $table->boolean('status')->default(true);
            $table->boolean('is_submitted')->default(false);
            $table->bigInteger('oddo_post_id')->nullable();
            $table->longText('odoo_failed_response')->nullable();
            $table->timestamp('mobile_created_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'invoice_number']);
            $table->index(['organisation_id', 'invoice_date']);
            $table->index(['organisation_id', 'current_stage']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
