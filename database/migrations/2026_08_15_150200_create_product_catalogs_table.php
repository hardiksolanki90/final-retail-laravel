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
     * Legacy column names flawer / shelf_file are intentional (keep as-is).
     */
    public function up(): void
    {
        Schema::create('product_catalogs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('item_id')->constrained('items');
            $table->string('barcode', 191)->nullable();
            $table->decimal('net_weight', 18, 2)->nullable();
            $table->string('flawer', 191)->nullable();
            $table->string('shelf_file', 191)->nullable();
            $table->string('ingredients', 191)->nullable();
            $table->string('energy', 191)->nullable();
            $table->string('fat', 191)->nullable();
            $table->string('protein', 191)->nullable();
            $table->string('carbohydrate', 191)->nullable();
            $table->string('calcium', 191)->nullable();
            $table->string('sodium', 191)->nullable();
            $table->string('potassium', 191)->nullable();
            $table->string('crude_fibre', 191)->nullable();
            $table->string('vitamin', 191)->nullable();
            $table->string('image_string', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_catalogs');
    }
};
