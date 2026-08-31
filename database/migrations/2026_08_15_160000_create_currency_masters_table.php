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
        Schema::create('currency_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('code', 191);
            $table->string('name_plural', 191);
            $table->string('symbol', 191);
            $table->string('symbol_native', 191);
            $table->bigInteger('decimal_digits');
            $table->bigInteger('rounding');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_masters');
    }
};
