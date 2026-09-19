<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clears the way for spatie/laravel-permission's own roles/permissions
 * tables (created by the migration right after this one). Pre-launch data
 * only — confirmed safe to wipe rather than migrate in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_flow_rule_approvers', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        // Old custom tables are gone for good once Spatie's replacements exist;
        // this migration is not meant to be reversed independently.
    }
};
