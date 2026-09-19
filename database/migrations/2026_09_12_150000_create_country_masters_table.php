<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global ISO country reference (schema matches the legacy
     * storage/backups/country_masters.sql export). Rows are imported by
     * DatabaseSeeder, not by this migration.
     */
    public function up(): void
    {
        Schema::create('country_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('dial_code', 10);
            $table->string('country_code', 10);
            $table->string('currency', 255);
            $table->string('currency_code', 10);
            $table->string('currency_symbol', 50);
            $table->string('alpha3', 10)->nullable();
            $table->string('tax_system', 191)->nullable();
            $table->string('tax_engine', 100)->nullable();
            $table->string('tax_name', 191)->nullable();
            $table->json('jurisdiction_level')->nullable();
            $table->decimal('default_rate', 5, 2)->nullable();
            $table->json('rate_range')->nullable();
            $table->json('components')->nullable();
            $table->string('registration_number_label', 191)->nullable();
            $table->text('calculation_notes')->nullable();
            $table->string('rate_source', 191)->nullable();
            $table->string('tax_status', 50)->nullable();
            $table->date('tax_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_masters');
    }
};
