<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->renameColumn('division_id', 'division_label_legacy');
        });

        // Seed a Division row per distinct legacy label, per organisation that
        // actually has pallet rows referencing it, then backfill a new FK
        // column from the match. Dev-only test data — correctness of the
        // migration path matters more than preserving every row.
        $legacyRows = DB::table('pallets')
            ->select('organisation_id', 'division_label_legacy')
            ->whereNotNull('division_label_legacy')
            ->distinct()
            ->get();

        $divisionIdByOrgAndLabel = [];

        foreach ($legacyRows as $row) {
            $uuid = (string) Str::uuid();
            $now = now();

            $id = DB::table('divisions')->insertGetId([
                'uuid' => $uuid,
                'organisation_id' => $row->organisation_id,
                'code' => null,
                'name' => $row->division_label_legacy,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $divisionIdByOrgAndLabel[$row->organisation_id.'|'.$row->division_label_legacy] = $id;
        }

        Schema::table('pallets', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('division_label_legacy')->constrained('divisions');
        });

        foreach ($divisionIdByOrgAndLabel as $key => $divisionId) {
            [$organisationId, $label] = explode('|', $key, 2);

            DB::table('pallets')
                ->where('organisation_id', $organisationId)
                ->where('division_label_legacy', $label)
                ->update(['division_id' => $divisionId]);
        }

        Schema::table('pallets', function (Blueprint $table) {
            $table->dropColumn('division_label_legacy');
        });
    }

    public function down(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->string('division_label_legacy', 191)->nullable();
        });

        DB::table('pallets')->orderBy('id')->each(function ($pallet) {
            if ($pallet->division_id === null) {
                return;
            }

            $name = DB::table('divisions')->where('id', $pallet->division_id)->value('name');

            DB::table('pallets')->where('id', $pallet->id)->update(['division_label_legacy' => $name]);
        });

        Schema::table('pallets', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });

        Schema::table('pallets', function (Blueprint $table) {
            $table->renameColumn('division_label_legacy', 'division_id');
        });
    }
};
