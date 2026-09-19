<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Routes were created before areas / depots / vans. Add the deferred FKs
     * plus tenant-scoped indexes. route_code is not unique across organisations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'route_code']);
            $table->index(['organisation_id', 'status']);

            if (! Schema::hasColumn('routes', 'van_id')) {
                $table->unsignedBigInteger('van_id')->nullable();
            }

            $table->foreign('area_id')->references('id')->on('areas');
            $table->foreign('depot_id')->references('id')->on('depots');
            $table->foreign('van_id')->references('id')->on('vans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['depot_id']);
            $table->dropForeign(['van_id']);

            $table->dropUnique(['organisation_id', 'uuid']);
            $table->dropIndex(['organisation_id', 'route_code']);
            $table->dropIndex(['organisation_id', 'status']);
        });
    }
};
