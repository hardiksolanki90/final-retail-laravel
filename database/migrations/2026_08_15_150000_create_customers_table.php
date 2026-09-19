<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Standalone `customers` table — no `user_id` / login fields. Column
     * naming follows the form (customerAdd.tsx) rather than the legacy
     * customer_infos prefixes (customer_address_1 -> address, etc.) since
     * this is a new table, not a port of the old dual-table design.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');

            $table->string('customer_code', 25);
            $table->string('shop_name', 191);
            $table->string('firstname', 191);
            $table->string('lastname', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 191)->nullable();

            $table->string('address', 191);
            $table->string('city', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('zipcode', 191)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->integer('credit_days')->default(0);
            $table->string('trn_no', 191)->nullable();

            $table->string('profile_image', 191)->nullable();
            $table->boolean('status')->default(true);

            $table->foreignId('route_id')->nullable()->constrained('routes');
            // Assigned salesman only — customers never log in, so this is not an auth user link.
            $table->foreignId('salesman_id')->nullable()->constrained('users');
            $table->foreignId('customer_type_id')->nullable()->constrained('customer_types');
            $table->foreignId('customer_category_id')->nullable()->constrained('customer_categories');
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups');
            $table->foreignId('channel_id')->nullable()->constrained('channels');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
