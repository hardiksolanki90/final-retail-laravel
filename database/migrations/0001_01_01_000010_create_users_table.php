<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Matches SalesmanAdd / auth user fields + documented users schema.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->nullable()->constrained('organisations');
            $table->unsignedBigInteger('usertype')->default(1)->comment('0:superadmin, 1:admin (organisation), 2:customer, 3:salesman...');
            $table->string('parent_id', 191)->nullable()->comment('If the user type is admin then put the admin id here to make it as a group.');
            $table->string('ad_id', 50)->nullable();
            $table->string('firstname', 191);
            $table->string('lastname', 191)->default('');
            $table->string('email', 191)->default('');
            $table->string('password', 191);
            $table->string('api_token', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile', 191)->nullable();
            $table->integer('country_id')->nullable();
            $table->boolean('is_approved_by_admin')->default(true);
            $table->boolean('status')->default(true);
            $table->string('id_stripe', 191)->nullable();
            $table->enum('login_type', ['system', 'google', 'facebook', 'twitter', 'mobile']);
            $table->integer('role_id')->default(2)->comment('1:superadmin, 2 org-admin, 3...');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
