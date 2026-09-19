<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_note_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('credit_note_id')->constrained('credit_notes');
            $table->foreignId('item_id')->constrained('items');
            $table->enum('item_condition', ['1', '2'])->default('1')->comment('1:Good, 2:Bad');
            $table->unsignedBigInteger('item_uom_id');
            $table->decimal('item_qty', 18, 2)->default(0);
            $table->decimal('item_price', 18, 2)->default(0);
            $table->decimal('item_gross', 18, 2)->default(0)->comment('item_qty * item_price');
            $table->decimal('item_discount_amount', 18, 2)->default(0);
            $table->decimal('item_net', 18, 2)->default(0)->comment('item_gross - item_discount_amount');
            $table->decimal('item_vat', 18, 2)->default(0);
            $table->decimal('item_excise', 18, 2)->default(0);
            $table->decimal('item_grand_total', 18, 2)->default(0)->comment('item_net + item_vat + item_excise');
            $table->string('batch_number', 191)->nullable();
            $table->string('reason', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['credit_note_id', 'item_id']);
            $table->index('item_uom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_details');
    }
};
