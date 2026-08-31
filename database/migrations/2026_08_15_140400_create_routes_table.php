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
     * area_id / depot_id / van_id are unsigned bigints without FKs until those
     * tables exist (same pattern as route_id on salesman_infos).
     */
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('depot_id');
            $table->unsignedBigInteger('van_id')->nullable();
            $table->string('route_code', 50);
            $table->string('route_name', 191);
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
        Schema::dropIfExists('routes');
    }
};
