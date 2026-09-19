<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('organisation_id')->constrained('areas');
        });
    }

    public function down(): void
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
    }
};
