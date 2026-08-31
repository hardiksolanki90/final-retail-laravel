<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Schema from docs/DATABASE_STRUCTURE.md.
     * item_uom_id is an unsigned bigint without FK until item_uoms is migrated.
     */
    public function up(): void
    {
        Schema::create('item_main_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('item_id')->constrained('items');
            $table->string('item_upc', 20);
            $table->unsignedBigInteger('item_uom_id');
            $table->boolean('item_shipping_uom')->default(false);
            $table->boolean('is_secondary')->default(false);
            $table->boolean('stock_keeping_unit')->default(false);
            $table->decimal('item_price', 18, 2)->default(0)->comment('default price');
            $table->decimal('purchase_order_price', 18, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_main_prices');
    }
};
