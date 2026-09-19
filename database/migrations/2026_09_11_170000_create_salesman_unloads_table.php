<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesman_unloads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('unload_number', 191);
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('van_id')->nullable()->constrained('vans');
            $table->foreignId('salesman_id')->constrained('users');
            $table->date('transaction_date');
            $table->boolean('status')->default(true);
            $table->enum('approval_status', ['Created', 'Updated', 'Deleted'])->default('Created');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'unload_number']);
            $table->index(['organisation_id', 'transaction_date']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman_unloads');
    }
};
