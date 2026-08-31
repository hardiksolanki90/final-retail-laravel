<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — order_details.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedBigInteger('item_uom_id');
            $table->unsignedBigInteger('original_item_uom_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_item_poi')->default(false);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->decimal('item_qty', 18, 2)->default(0);
            $table->decimal('item_weight', 8, 2)->default(0);
            $table->decimal('item_price', 18, 2)->default(0);
            $table->decimal('item_gross', 18, 2)->default(0)->comment('item_qty * item_price');
            $table->decimal('item_discount_amount', 18, 2)->default(0);
            $table->decimal('item_net', 18, 2)->default(0)->comment('item_gross - item_discount_amount');
            $table->decimal('item_vat', 18, 2)->default(0);
            $table->decimal('item_excise', 18, 2)->default(0);
            $table->decimal('item_grand_total', 18, 2)->default(0)->comment('item_net + item_vat + item_excise');
            $table->decimal('delivered_qty', 18, 2)->default(0);
            $table->decimal('open_qty', 18, 2)->default(0);
            $table->decimal('original_item_qty', 8, 2)->default(0);
            $table->decimal('original_item_price', 8, 2)->default(0);
            $table->string('item_vendor_code', 191)->nullable();
            $table->decimal('request_qty', 8, 2)->default(0);
            $table->enum('order_status', ['Pending', 'Delivered', 'Partial-Delivered'])->default('Pending');
            $table->enum('picking_status', ['partial', 'full'])->nullable();
            $table->enum('transportation_status', ['No', 'Delegated'])->nullable();
            $table->enum('shipment_status', ['partial', 'full'])->nullable();
            $table->enum('invoice_status', ['partial', 'full'])->nullable();
            $table->boolean('is_rfgen_sync')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_picking')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['order_id', 'item_id']);
            $table->index('item_uom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
