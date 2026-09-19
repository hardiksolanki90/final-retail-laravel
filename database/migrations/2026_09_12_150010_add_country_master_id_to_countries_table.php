<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * countries now holds the country(ies) an organisation has selected —
     * this links each one back to its country_masters reference row.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('country_master_id')->nullable()->after('organisation_id')->constrained('country_masters');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_master_id');
        });
    }
};
