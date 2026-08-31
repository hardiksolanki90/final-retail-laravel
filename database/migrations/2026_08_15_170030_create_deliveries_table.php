<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — deliveries.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('users');
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->unsignedBigInteger('storage_location_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->unsignedBigInteger('lob_id')->nullable();
            $table->unsignedBigInteger('delivery_type')->comment('from order type table');
            $table->enum('delivery_type_source', ['1', '2']);
            $table->string('delivery_number', 191);
            $table->string('invoice_number', 191)->nullable();
            $table->unsignedBigInteger('invoice_route_id')->nullable();
            $table->date('delivery_date');
            $table->date('change_date')->nullable();
            $table->time('delivery_time');
            $table->date('delivery_due_date');
            $table->string('delivery_weight', 191)->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->decimal('total_qty', 18, 2)->default(0);
            $table->decimal('total_cancel_qty', 8, 2)->default(0);
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_discount_amount', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0)->comment('total_gross - total_discount_amount');
            $table->decimal('total_vat', 18, 2)->default(0);
            $table->decimal('total_excise', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0)->comment('total_net + total_vat + total_excise');
            $table->enum('current_stage', [
                'Pending', 'Approved', 'Rejected', 'In-Process', 'Completed',
            ])->default('Pending');
            $table->text('current_stage_comment')->nullable();
            $table->enum('approval_status', [
                'Deleted', 'Created', 'Updated', 'In-Process', 'Partial-Invoiced',
                'Completed', 'Cancel', 'Shipment', 'Truck Allocated', 'Picked',
            ])->default('Created');
            $table->integer('source')->comment('1:Mobile, 2:Backend, 3:Frontend');
            $table->boolean('status')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_truck_allocated')->default(false);
            $table->text('sync_status')->nullable();
            $table->enum('picking_status', ['partial', 'full'])->nullable();
            $table->enum('transportation_status', ['No', 'Delegated'])->nullable();
            $table->enum('shipment_status', ['partial', 'full'])->nullable();
            $table->enum('invoice_status', ['partial', 'full'])->nullable();
            $table->boolean('is_user_updated')->default(false);
            $table->bigInteger('user_updated')->nullable();
            $table->string('module_updated', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'delivery_number']);
            $table->index(['organisation_id', 'delivery_date']);
            $table->index(['organisation_id', 'current_stage']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
