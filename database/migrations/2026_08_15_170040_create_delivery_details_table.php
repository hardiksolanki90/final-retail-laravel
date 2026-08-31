<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — delivery_details.
     */
    public function up(): void
    {
        Schema::create('delivery_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('delivery_id')->constrained('deliveries');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedBigInteger('item_uom_id');
            $table->unsignedBigInteger('original_item_uom_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_item_poi')->default(false);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->decimal('item_qty', 18, 2)->default(0);
            $table->decimal('original_item_id', 8, 2)->default(0);
            $table->decimal('item_price', 18, 2)->default(0);
            $table->decimal('item_gross', 18, 2)->default(0)->comment('item_qty * item_price');
            $table->decimal('item_discount_amount', 18, 2)->default(0);
            $table->decimal('item_net', 18, 2)->default(0)->comment('item_gross - item_discount_amount');
            $table->decimal('item_vat', 18, 2)->default(0);
            $table->decimal('item_excise', 18, 2)->default(0);
            $table->decimal('item_grand_total', 18, 2)->default(0)->comment('item_net + item_vat + item_excise');
            $table->string('batch_number', 191)->nullable();
            $table->decimal('invoiced_qty', 18, 2)->default(0);
            $table->decimal('open_qty', 18, 2)->default(0);
            $table->decimal('original_item_qty', 8, 2)->default(0);
            $table->decimal('cancel_qty', 8, 2)->default(0);
            $table->enum('delivery_status', [
                'Pending', 'Invoiced', 'Partial-Invoiced', 'Cancelled',
            ])->default('Pending');
            $table->enum('picking_status', ['partial', 'full'])->nullable();
            $table->enum('transportation_status', ['No', 'Delegated'])->nullable();
            $table->enum('shipment_status', ['partial', 'full'])->nullable();
            $table->enum('invoice_status', ['partial', 'full'])->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_picking')->default(false);
            $table->unsignedBigInteger('delivery_note_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['delivery_id', 'item_id']);
            $table->index('item_uom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_details');
    }
};
