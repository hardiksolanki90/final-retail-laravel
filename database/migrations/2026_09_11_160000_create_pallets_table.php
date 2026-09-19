<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->date('date');
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('item_id')->constrained('items');
            $table->string('division_id', 191)->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->decimal('qty', 18, 2)->default(0);
            $table->enum('pallet_type', ['allocated', 'return'])->default('allocated');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'salesman_id']);
            $table->index(['organisation_id', 'pallet_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pallets');
    }
};
