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
            $table->foreignId('organisation_id')->nullable()->constrained('organisations');
            $table->string('customer_category_code', 191)->comment('like CC01, CC02 etc.');
            $table->foreignId('parent_id')->nullable()->constrained('customer_categories');
            $table->bigInteger('node_level')->default(0);
            $table->string('customer_category_name', 191)->comment('like agent, depo etc.');
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
        Schema::dropIfExists('customer_categories');
    }
};
