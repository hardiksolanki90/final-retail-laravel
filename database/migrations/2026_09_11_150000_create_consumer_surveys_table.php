<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_surveys', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('survey_code', 191);
            $table->string('survey_name', 191);
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('merchandiser_id')->constrained('users');
            $table->date('date');
            $table->json('questions')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'survey_code']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_surveys');
    }
};
