<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesman_unload_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('salesman_unload_id')->constrained('salesman_unloads');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedBigInteger('item_uom_id')->nullable();
            $table->decimal('unload_qty', 18, 2)->default(0);
            $table->enum('unload_type', ['fresh', 'damage', 'expired'])->default('fresh');
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['salesman_unload_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_unload_details');
    }
};
