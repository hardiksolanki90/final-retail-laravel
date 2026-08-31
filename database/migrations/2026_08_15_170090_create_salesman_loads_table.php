<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema from docs/DATABASE_STRUCTURE.md — salesman_loads.
     */
    public function up(): void
    {
        Schema::create('salesman_loads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('load_number', 191);
            $table->foreignId('depot_id')->nullable()->constrained('depots');
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->bigInteger('trip_id')->nullable();
            $table->smallInteger('trip_number')->default(1);
            $table->foreignId('van_id')->nullable()->constrained('vans');
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('salesman_id')->constrained('users');
            $table->unsignedBigInteger('storage_location_id')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->date('load_date');
            $table->unsignedBigInteger('load_type')->nullable()->comment('1: Delivery Load 2: Van Load');
            $table->boolean('load_confirm')->default(true)->comment('0 Pending, 1 Confirm');
            $table->boolean('status')->default(true);
            $table->enum('approval_status', [
                'Deleted', 'Created', 'Updated', 'In-Process', 'Completed',
                'Cancel', 'Shipment', 'Truck Allocated', 'Picked',
            ])->default('Created');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'load_number']);
            $table->index(['organisation_id', 'load_date']);
            $table->index(['organisation_id', 'approval_status']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_loads');
    }
};
