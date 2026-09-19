<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('good_receipt_note_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('good_receipt_note_id')->constrained('good_receipt_notes');
            $table->foreignId('item_id')->constrained('items');
            $table->unsignedBigInteger('item_uom_id')->nullable();
            $table->decimal('qty', 18, 2)->default(0);
            $table->unsignedBigInteger('reason_id')->nullable();
            $table->unsignedBigInteger('return_reason_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->index(['good_receipt_note_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('good_receipt_note_details');
    }
};
