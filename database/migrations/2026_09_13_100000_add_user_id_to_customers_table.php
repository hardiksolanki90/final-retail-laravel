<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional portal login for a customer. Nullable + unique = optional
     * one-to-one (a customer may or may not have a linked user account, and
     * a user account belongs to at most one customer). nullOnDelete rather
     * than cascade — if the linked user is ever hard-deleted, unlink instead
     * of destroying the customer's business record.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('organisation_id')
                ->unique()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
