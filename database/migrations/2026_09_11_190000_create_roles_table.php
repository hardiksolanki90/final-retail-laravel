<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            // Nullable: the seeded system roles (super-admin/org-admin/salesman/
            // customer) are global; an org-admin can additionally define custom
            // roles scoped to their own organisation.
            $table->foreignId('organisation_id')->nullable()->constrained('organisations');
            $table->string('code', 191)->nullable();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
