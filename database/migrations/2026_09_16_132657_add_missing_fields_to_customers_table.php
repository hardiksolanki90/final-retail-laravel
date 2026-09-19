<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_office_address')->nullable();
            $table->string('customer_office_city')->nullable();
            $table->string('customer_office_state')->nullable();
            $table->string('customer_office_zipcode')->nullable();
            $table->string('customer_office_phone')->nullable();

            $table->string('customer_office_lat')->nullable();
            $table->string('customer_office_lang')->nullable();

            $table->string('customer_home_address')->nullable();
            $table->string('customer_home_lat')->nullable();
            $table->string('customer_home_lang')->nullable();

            $table->unsignedBigInteger('sales_organisation_id')->nullable();

            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('region_id')->nullable()->constrained('regions');
            $table->foreignId('merchandiser_id')->nullable()->constrained('users');

            $table->foreignId('ship_to_party_id')->nullable()->constrained('customers');
            $table->foreignId('sold_to_party_id')->nullable()->constrained('customers');
            $table->foreignId('payer_id')->nullable()->constrained('customers');
            $table->foreignId('bill_to_party_id')->nullable()->constrained('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['merchandiser_id']);
            $table->dropForeign(['ship_to_party_id']);
            $table->dropForeign(['sold_to_party_id']);
            $table->dropForeign(['payer_id']);
            $table->dropForeign(['bill_to_party_id']);

            $table->dropColumn([
                'customer_office_address',
                'customer_office_city',
                'customer_office_state',
                'customer_office_zipcode',
                'customer_office_phone',
                'customer_office_lat',
                'customer_office_lang',
                'customer_home_address',
                'customer_home_lat',
                'customer_home_lang',
                'sales_organisation_id',
                'country_id',
                'region_id',
                'merchandiser_id',
                'ship_to_party_id',
                'sold_to_party_id',
                'payer_id',
                'bill_to_party_id',
            ]);
        });
    }
};
