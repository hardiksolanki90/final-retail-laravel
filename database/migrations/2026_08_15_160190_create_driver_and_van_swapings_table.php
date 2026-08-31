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
     * order_id is an unsigned bigint without a FK (orders table out of scope).
     */
    public function up(): void
    {
        Schema::create('driver_and_van_swapings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('new_salesman_id')->nullable()->constrained('users');
            $table->foreignId('old_salesman_id')->nullable()->constrained('users');
            $table->foreignId('old_van_id')->nullable()->constrained('vans');
            $table->foreignId('new_van_id')->nullable()->constrained('vans');
            $table->foreignId('login_user_id')->constrained('users')->comment('Which user changed this record, for log');
            $table->foreignId('reason_id')->nullable()->constrained('reason_types');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_and_van_swapings');
    }
};
