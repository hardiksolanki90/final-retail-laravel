<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — invoice_details.
     */
    public function up(): void
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedBigInteger('item_uom_id');
            $table->foreignId('van_id')->nullable()->constrained('vans');
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_item_poi')->default(false);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->decimal('item_qty', 18, 2)->default(0);
            $table->decimal('lower_unit_qty', 18, 2)->default(0);
            $table->decimal('item_price', 18, 2)->default(0);
            $table->decimal('item_gross', 18, 2)->default(0)->comment('item_qty * item_price');
            $table->decimal('item_discount_amount', 18, 2)->default(0);
            $table->decimal('item_net', 18, 2)->default(0)->comment('item_gross - item_discount_amount');
            $table->decimal('item_vat', 18, 2)->default(0);
            $table->decimal('item_excise', 18, 2)->default(0);
            $table->decimal('item_grand_total', 18, 2)->default(0)->comment('item_net + item_vat + item_excise');
            $table->decimal('base_price', 8, 2)->default(0);
            $table->string('batch_number', 191)->nullable();
            $table->decimal('original_item_qty', 8, 2)->default(0);
            $table->integer('erp_post_id')->nullable();
            $table->longText('erp_response_error')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->bigInteger('delv_id')->nullable();
            $table->tinyInteger('deleted_import_data')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->date('import_date')->nullable();

            $table->unique('uuid');
            $table->index(['invoice_id', 'item_id']);
            $table->index('item_uom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
