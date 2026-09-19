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
        Schema::table('bank_information', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('account_number');
            $table->string('swift_code')->nullable()->after('iban');
            $table->string('ifsc_code')->nullable()->after('swift_code');
            $table->string('routing_number')->nullable()->after('ifsc_code');
            $table->string('sort_code')->nullable()->after('routing_number');
            $table->string('branch_name')->nullable()->after('sort_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_information', function (Blueprint $table) {
            $table->dropColumn([
                'iban',
                'swift_code',
                'ifsc_code',
                'routing_number',
                'sort_code',
                'branch_name',
            ]);
        });
    }
};
