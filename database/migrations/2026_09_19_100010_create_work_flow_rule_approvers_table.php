<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_flow_rule_approvers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('work_flow_rule_id')->constrained('work_flow_rules')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_flow_rule_approvers');
    }
};
