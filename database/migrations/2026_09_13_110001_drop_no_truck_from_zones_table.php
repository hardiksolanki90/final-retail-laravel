<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `no_truck` exists on the live `zones` table (NOT NULL, no default) but
     * was never declared in create_zones_table's migration file — schema
     * drift from an untracked manual change. The app stopped referencing it
     * (Zone model/repository/requests), so every insert was failing on this
     * NOT NULL column with nothing supplied. Drop it for real here.
     */
    public function up(): void
    {
        if (Schema::hasColumn('zones', 'no_truck')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->dropColumn('no_truck');
            });
        }
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->integer('no_truck')->default(0);
        });
    }
};
