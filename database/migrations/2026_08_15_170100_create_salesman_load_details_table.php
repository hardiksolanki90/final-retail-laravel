<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — salesman_load_details.
     */
    public function up(): void
    {
        Schema::create('salesman_load_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('salesman_load_id')->nullable()->constrained('salesman_loads');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->foreignId('depot_id')->nullable()->constrained('depots');
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->foreignId('salesman_id')->nullable()->constrained('users');
            $table->unsignedBigInteger('storage_location_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('van_id')->nullable()->constrained('vans');
            $table->unsignedBigInteger('dat_id')->nullable();
            $table->date('load_date');
            $table->date('change_date')->nullable();
            $table->string('item_uom', 191);
            $table->string('load_qty', 191);
            $table->decimal('lower_qty', 8, 2)->default(0);
            $table->decimal('ctn_qty', 18, 2)->default(0);
            $table->decimal('requested_qty', 18, 2)->default(0);
            $table->unsignedBigInteger('requested_item_uom_id')->nullable();
            $table->enum('is_exported', ['No', 'Yes'])->default('No');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['salesman_load_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_load_details');
    }
};
