<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('journey_name', 191);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->boolean('no_end')->default(false);
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('journey_plan_base', ['day_wise', 'week_wise'])->default('day_wise');
            $table->json('selected_weeks')->nullable();
            $table->enum('first_day_of_week', [
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            ])->default('monday');
            $table->boolean('enforce_flag')->default(false);
            $table->foreignId('merchandiser_id')->constrained('users');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_plans');
    }
};
