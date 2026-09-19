<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable on add (existing rows have none yet) — required going forward
     * at the request-validation layer, matching how zone_code/region_code/route_code
     * are handled elsewhere in this codebase.
     */
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->string('area_code', 50)->nullable()->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('area_code');
        });
    }
};
