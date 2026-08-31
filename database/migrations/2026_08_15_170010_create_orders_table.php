<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — orders.
     * order_type_id / lob_id / storage_location_id are unsigned bigints without
     * constraints until those tables are migrated. invoice_id FK added later.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('depot_id')->nullable()->constrained('depots');
            $table->unsignedBigInteger('order_type_id');
            $table->foreignId('salesman_id')->nullable()->constrained('users');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->unsignedBigInteger('storage_location_id')->default(0);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->unsignedBigInteger('lob_id')->nullable();
            $table->string('erp_number', 50)->nullable();
            $table->string('customer_lop', 191)->nullable();
            $table->string('order_number', 191);
            $table->date('order_date');
            $table->date('due_date');
            $table->date('delivery_date')->nullable();
            $table->date('change_date')->nullable();
            $table->bigInteger('hold_reason')->nullable();
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');
            $table->decimal('total_qty', 18, 2)->default(0);
            $table->decimal('total_cancel_qty', 8, 2)->default(0);
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_discount_amount', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0)->comment('total_gross - total_discount_amount');
            $table->decimal('total_vat', 18, 2)->default(0);
            $table->decimal('total_excise', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0)->comment('total_net + total_vat + total_excise');
            $table->text('any_comment')->nullable();
            $table->enum('current_stage', [
                'Pending', 'Approved', 'Rejected', 'In-Process', 'Partial-Deliver',
                'Completed', 'Shipping', 'Cancelled', 'Picking',
            ])->default('Pending');
            $table->text('current_stage_comment')->nullable();
            $table->enum('approval_status', [
                'Deleted', 'Created', 'Updated', 'In-Process', 'Shipment', 'Delivered',
                'Completed', 'Cancelled', 'Picking Confirmed', 'Picked', 'Truck Allocated',
            ])->default('Created')->nullable();
            $table->string('sign_image', 191)->nullable();
            $table->integer('source')->comment('1:Mobile, 2:Backend, 3:Frontend');
            $table->boolean('status')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->text('sync_status')->nullable();
            $table->foreignId('order_created_user_id')->nullable()->constrained('users');
            $table->enum('order_status', ['created', 'partial', 'full'])->default('created');
            $table->boolean('order_generate_picking')->default(false);
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->enum('picking_status', ['partial', 'full'])->nullable();
            $table->enum('transportation_status', ['No', 'Delegated'])->nullable();
            $table->enum('shipment_status', ['partial', 'full'])->nullable();
            $table->enum('invoice_status', ['partial', 'full'])->nullable();
            $table->boolean('is_user_updated')->default(false);
            $table->bigInteger('user_updated')->nullable();
            $table->string('module_updated', 191)->nullable();
            $table->boolean('is_presale_order')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'order_number']);
            $table->index(['organisation_id', 'order_date']);
            $table->index(['organisation_id', 'current_stage']);
            $table->index(['organisation_id', 'status']);
            // invoice_id is unsignedBigInteger until deferred FK migration
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
