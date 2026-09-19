<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.role_id was a plain `integer` with no FK (doctrine/dbal isn't
     * installed, so Schema::table(...)->change() isn't available — the type
     * change is done via raw SQL instead).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY role_id BIGINT UNSIGNED NOT NULL DEFAULT 2');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        DB::statement('ALTER TABLE users MODIFY role_id INT NOT NULL DEFAULT 2');
    }
};
