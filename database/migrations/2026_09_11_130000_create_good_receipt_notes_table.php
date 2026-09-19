<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('good_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organisation_id')->constrained('organisations');
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses');
            $table->string('grn_number', 191);
            $table->date('grn_date');
            $table->text('grn_remark')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organisation_id', 'uuid']);
            $table->unique(['organisation_id', 'grn_number']);
            $table->index(['organisation_id', 'grn_date']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('good_receipt_notes');
    }
};
