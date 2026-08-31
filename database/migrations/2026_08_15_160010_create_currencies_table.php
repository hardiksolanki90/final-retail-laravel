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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('currency_master_id')->constrained('currency_masters');
            $table->string('name', 191);
            $table->char('symbol', 10);
            $table->string('code', 191);
            $table->string('name_plural', 191);
            $table->char('symbol_native', 10);
            $table->bigInteger('decimal_digits');
            $table->bigInteger('rounding');
            $table->boolean('default_currency')->default(false);
            $table->enum('format', ['1,234,567.89', '1.234.567.89', '1 234 567.89']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
