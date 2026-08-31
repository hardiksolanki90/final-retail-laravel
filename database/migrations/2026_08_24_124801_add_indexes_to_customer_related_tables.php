<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant-scoped indexes for CustomerAdd lookup tables.
     * Codes are not unique — the same code may exist in many organisations.
     */
    public function up(): void
    {
        Schema::table('customer_types', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index('customer_type_code');
            $table->index('status');
        });

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->unique('uuid');
            $table->index(['organisation_id', 'customer_category_code']);
            $table->index(['organisation_id', 'status']);
        });

        Schema::table('customer_groups', function (Blueprint $table) {
            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'group_code']);
            $table->index(['organisation_id', 'status']);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'status']);
        });

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->dropUnique(['organisation_id', 'uuid']);
            $table->dropIndex(['organisation_id', 'status']);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropUnique(['organisation_id', 'uuid']);
            $table->dropIndex(['organisation_id', 'status']);
        });

        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropUnique(['organisation_id', 'uuid']);
            $table->dropIndex(['organisation_id', 'group_code']);
            $table->dropIndex(['organisation_id', 'status']);
        });

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropIndex(['organisation_id', 'customer_category_code']);
            $table->dropIndex(['organisation_id', 'status']);
        });

        Schema::table('customer_types', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropIndex(['customer_type_code']);
            $table->dropIndex(['status']);
        });
    }
};
