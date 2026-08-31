<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Full documented schema (docs/DATABASE_STRUCTURE.md) so fresh installs
     * already have designation/employee_code/supervisor_id — the later
     * add_salesman_infos_missing_fields migration stays a no-op here.
     */
    public function up(): void
    {
        Schema::create('salesman_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('salesman_helper_id')->nullable()->comment("It's coming from users table");
            $table->unsignedBigInteger('salesman_type_id');
            $table->unsignedBigInteger('salesman_role_id');
            $table->string('designation', 191)->nullable();
            $table->string('category_id', 50)->nullable()->comment('1: Salesman, 2: Salesman cum driver, 3: Helper, 4: Driver cum helper');
            $table->string('salesman_code', 20);
            $table->string('employee_code', 50)->nullable();
            $table->string('salesman_supervisor', 191)->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->bigInteger('asm_id')->nullable();
            $table->bigInteger('nsm_id')->nullable();
            $table->date('date_of_joning')->nullable();
            $table->boolean('is_block')->default(false);
            $table->date('block_start_date')->nullable();
            $table->date('block_end_date')->nullable();
            $table->boolean('status')->default(false);
            $table->string('profile_image', 191)->nullable();
            $table->decimal('incentive', 8, 3)->default(0.000);
            $table->enum('current_stage', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('current_stage_comment')->nullable();
            $table->boolean('is_lob')->default(false)->comment('1=LOB');
            $table->integer('printer_config')->default(1)->comment('1: Zebra, 2: honeywell');
            $table->integer('geo_flag')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman_infos');
    }
};
