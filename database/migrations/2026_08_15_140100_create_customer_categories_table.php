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
        Schema::create('customer_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->string('customer_category_code')->comment('like CC01, CC02 etc.');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->bigInteger('node_level')->default(0);
            $table->string('customer_category_name')->comment('like agent, depo etc.');
            $table->boolean('status')->default(1);
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('customer_categories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_categories');
    }
};
