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
        Schema::create('van_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('name', 191);
            $table->foreignId('parent_id')->nullable()->constrained('van_categories');
            $table->bigInteger('node_level')->default(0);
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
        Schema::dropIfExists('van_categories');
    }
};
