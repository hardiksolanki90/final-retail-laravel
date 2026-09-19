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
            $table->unsignedBigInteger('organisation_id');
            $table->string('van_code', 25);
            $table->string('plate_number', 15);
            $table->string('description');
            $table->integer('capacity')->nullable();
            $table->integer('van_type_id');
            $table->integer('van_category_id')->nullable();
            $table->boolean('van_status')->default(1);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
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
