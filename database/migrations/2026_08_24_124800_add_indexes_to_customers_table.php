<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant-scoped indexes for CustomerList search/filters.
     * customer_code is not unique — the same code may exist in many organisations.
     * uuid is unique per organisation so API lookups stay tenant-safe.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'customer_code']);

            $table->index(['organisation_id', 'status']);
            $table->index(['organisation_id', 'salesman_id']);
            $table->index(['organisation_id', 'route_id']);
            $table->index(['organisation_id', 'customer_type_id']);
            $table->index(['organisation_id', 'customer_category_id']);
            $table->index(['organisation_id', 'customer_group_id']);
            $table->index(['organisation_id', 'channel_id']);
            $table->index(['organisation_id', 'payment_term_id']);

            $table->index('shop_name');
            $table->index('firstname');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['organisation_id', 'uuid']);
            $table->dropIndex(['organisation_id', 'customer_code']);

            $table->dropIndex(['organisation_id', 'status']);
            $table->dropIndex(['organisation_id', 'salesman_id']);
            $table->dropIndex(['organisation_id', 'route_id']);
            $table->dropIndex(['organisation_id', 'customer_type_id']);
            $table->dropIndex(['organisation_id', 'customer_category_id']);
            $table->dropIndex(['organisation_id', 'customer_group_id']);
            $table->dropIndex(['organisation_id', 'channel_id']);
            $table->dropIndex(['organisation_id', 'payment_term_id']);

            $table->dropIndex(['shop_name']);
            $table->dropIndex(['firstname']);
            $table->dropIndex(['phone']);
        });
    }
};
