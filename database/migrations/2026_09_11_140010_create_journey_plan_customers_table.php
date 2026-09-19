<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_plan_customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('journey_plan_id')->constrained('journey_plans');
            $table->enum('day_of_week', [
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            ]);
            $table->unsignedInteger('sequence')->default(0);
            $table->foreignId('customer_id')->constrained('customers');
            $table->boolean('msl_perform')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['journey_plan_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_plan_customers');
    }
};
