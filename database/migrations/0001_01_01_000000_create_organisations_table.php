<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Matches OrganisationAdd form fields + documented organisations schema.
     */
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('reg_software_id');
            $table->string('org_name', 191);
            $table->string('org_company_id', 191);
            $table->string('org_tax_id', 191)->nullable();
            $table->string('org_street1', 191);
            $table->string('org_street2', 191)->nullable();
            $table->string('org_city', 191)->nullable();
            $table->string('org_state', 191)->nullable();
            $table->integer('org_country_id');
            $table->string('org_postal', 191)->nullable();
            $table->string('org_phone', 191);
            $table->string('org_contact_person', 191)->nullable();
            $table->string('org_contact_person_number', 191)->nullable();
            $table->string('org_currency', 191)->default('USD');
            $table->string('org_fasical_year', 191)->nullable();
            $table->boolean('is_batch_enabled')->default(false);
            $table->boolean('is_credit_limit_enabled')->default(false);
            $table->string('org_logo', 191)->default('assets/organisation/no-image.png');
            $table->string('gstin_number', 50);
            $table->string('gst_reg_date', 50);
            $table->boolean('is_auto_approval_set')->default(false);
            $table->boolean('org_status')->default(true);
            $table->boolean('is_trial_period')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
