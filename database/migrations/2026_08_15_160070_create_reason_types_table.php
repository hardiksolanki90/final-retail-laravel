<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Schema from docs/DATABASE_STRUCTURE.md.
     */
    public function up(): void
    {
        Schema::create('reason_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->string('name', 191);
            $table->enum('type', [
                'Non Service Reason',
                'Good Return Reason',
                'Bad Return Reason',
                'Debit Note Reason',
                'Visit Reason',
                'Receipt Reason',
                'Order',
                'Delivery',
                'CreditNote',
                'SalesmanLoad',
                'GoodReturnNote',
                'Order Process Reason',
                'Delivery Reason',
            ]);
            $table->string('code', 191)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reason_types');
    }
};
