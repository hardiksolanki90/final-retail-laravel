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
        Schema::create('depots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('user_id')->nullable()->constrained('users')->comment('this is an agent');
            $table->foreignId('region_id')->constrained('regions');
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->string('depot_code', 20);
            $table->string('depot_name', 100);
            $table->string('depot_manager', 191);
            $table->string('depot_manager_contact', 50)->nullable();
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
        Schema::dropIfExists('depots');
    }
};
