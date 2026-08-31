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
     * Lookup FKs (item_major_category_id, item_group_id, brand_id, channel_id,
     * lower_unit_uom_id) are unsigned bigints without constraints until those
     * tables are migrated.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->unsignedBigInteger('item_major_category_id');
            $table->unsignedBigInteger('item_group_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->boolean('is_product_catalog')->default(false);
            $table->boolean('is_promotional')->default(false);
            $table->string('item_code', 191);
            $table->string('erp_code', 50)->nullable();
            $table->string('item_name', 191);
            $table->string('item_description', 191)->nullable();
            $table->string('item_barcode', 191)->nullable();
            $table->decimal('item_weight', 18, 2)->default(0);
            $table->string('item_shelf_life', 191)->nullable();
            $table->decimal('volume', 18, 2)->default(0);
            $table->integer('lower_unit_item_upc');
            $table->unsignedBigInteger('lower_unit_uom_id')->comment('which UOM is lower unit.');
            $table->decimal('lower_unit_item_price', 18, 2);
            $table->decimal('lower_unit_purchase_order_price', 18, 2);
            $table->boolean('item_shipping_uom')->default(false);
            $table->boolean('is_tax_apply')->default(true);
            $table->decimal('item_vat_percentage', 5, 2)->default(0);
            $table->boolean('is_item_excise')->default(false);
            $table->decimal('item_excise', 5, 2)->default(0);
            $table->unsignedBigInteger('item_excise_uom_id')->default(0);
            $table->boolean('new_lunch')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('current_stage', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('current_stage_comment')->nullable();
            $table->string('item_image', 300)->nullable();
            $table->boolean('stock_keeping_unit')->default(false);
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
        Schema::dropIfExists('items');
    }
};
