<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * routes.beat_id was never consumed anywhere in the app (no reports,
     * customer assignment, or journey planning reads it) and has no nfpc
     * precedent — Route ties to Area + Depot directly, matching nfpc.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['beat_id']);
            $table->dropColumn('beat_id');
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->foreignId('beat_id')->nullable()->after('area_id')->constrained('beats')->nullOnDelete();
        });
    }
};
