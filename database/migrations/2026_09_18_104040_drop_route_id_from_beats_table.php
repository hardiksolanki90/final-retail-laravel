<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * beats.route_id was the original Beat->Route link (create_beats_table);
     * it was superseded by routes.beat_id (add_beat_id_to_routes_table) and
     * the Beat model never referenced this column. Drop the orphaned FK/column.
     */
    public function up(): void
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropColumn('route_id');
        });
    }

    public function down(): void
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->foreignId('route_id')->nullable()->constrained('routes');
        });
    }
};
