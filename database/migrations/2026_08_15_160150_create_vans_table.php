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
        Schema::create('vans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('van_code', 25);
            $table->string('plate_number', 15);
            $table->string('description', 191);
            $table->integer('capacity')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('van_type_id')->constrained('van_types');
            $table->foreignId('van_category_id')->nullable()->constrained('van_categories');
            $table->boolean('van_status')->default(true);
            $table->bigInteger('reading')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vans');
    }
};
