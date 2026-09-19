<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * type was a GST-only enum (CGST/SGST/IGST/UTGST/Cess). Widened to a
     * plain string so it can hold any country's tax components (VAT, Sales
     * Tax sub-types, etc.) — allowed values are now enforced dynamically in
     * StoreTaxRateRequest/UpdateTaxRateRequest based on the organisation's
     * country tax profile, not a fixed DB enum.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE tax_rates MODIFY type VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tax_rates MODIFY type ENUM('CGST','SGST','IGST','UTGST','Cess') NOT NULL");
    }
};
