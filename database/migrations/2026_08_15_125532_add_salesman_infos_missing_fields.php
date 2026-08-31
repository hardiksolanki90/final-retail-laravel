<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds fields required by the frontend Salesman Add/Edit form (SalesmanAdd.tsx)
 * that are missing from salesman_infos.
 *
 * - employee_code: no column existed (only salesman_code did)
 * - designation:   no column existed
 * - supervisor_id: FK to users; legacy salesman_supervisor (varchar) left untouched
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasDesignation = Schema::hasColumn('salesman_infos', 'designation');
        $hasEmployeeCode = Schema::hasColumn('salesman_infos', 'employee_code');
        $hasSupervisorId = Schema::hasColumn('salesman_infos', 'supervisor_id');

        if ($hasDesignation && $hasEmployeeCode && $hasSupervisorId) {
            return;
        }

        Schema::table('salesman_infos', function (Blueprint $table) use ($hasDesignation, $hasEmployeeCode, $hasSupervisorId) {
            if (! $hasDesignation) {
                $table->string('designation', 191)->nullable()->after('salesman_role_id');
            }

            if (! $hasEmployeeCode) {
                $table->string('employee_code', 50)->nullable()->after('salesman_code');
            }

            if (! $hasSupervisorId) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('salesman_supervisor');
                $table->foreign('supervisor_id', 'salesman_infos_supervisor_id_foreign')
                    ->references('id')
                    ->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasDesignation = Schema::hasColumn('salesman_infos', 'designation');
        $hasEmployeeCode = Schema::hasColumn('salesman_infos', 'employee_code');
        $hasSupervisorId = Schema::hasColumn('salesman_infos', 'supervisor_id');

        if (! $hasDesignation && ! $hasEmployeeCode && ! $hasSupervisorId) {
            return;
        }

        Schema::table('salesman_infos', function (Blueprint $table) use ($hasDesignation, $hasEmployeeCode, $hasSupervisorId) {
            if ($hasSupervisorId) {
                $table->dropForeign('salesman_infos_supervisor_id_foreign');
                $table->dropColumn('supervisor_id');
            }

            if ($hasDesignation) {
                $table->dropColumn('designation');
            }

            if ($hasEmployeeCode) {
                $table->dropColumn('employee_code');
            }
        });
    }
};
