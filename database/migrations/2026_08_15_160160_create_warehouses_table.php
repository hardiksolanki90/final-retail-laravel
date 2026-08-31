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
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('code', 191);
            $table->string('name', 191);
            $table->string('address', 191)->nullable();
            $table->string('manager', 191)->nullable();
            $table->boolean('is_main')->default(false);
            $table->integer('loc_type')->nullable();
            $table->string('lat', 191)->nullable();
            $table->string('lang', 191)->nullable();
            $table->foreignId('depot_id')->nullable()->constrained('depots')
                ->comment('if warehouse is parent then this will be null');
            $table->foreignId('route_id')->nullable()->constrained('routes')
                ->comment('if warehouse is parent then this will be null');
            $table->foreignId('parent_warehouse_id')->nullable()->constrained('warehouses');
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
