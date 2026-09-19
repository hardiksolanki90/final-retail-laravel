<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Beat;
use App\Models\Organisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BeatSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            $org = Organisation::firstOrCreate(
                ['org_name' => 'NFPC / Final Retail'],
                [
                    'uuid' => (string) Str::uuid(),
                    'reg_software_id' => 1,
                    'org_company_id' => 'ORG001',
                    'org_street1' => 'Dubai Industrial Park',
                    'org_city' => 'Dubai',
                    'org_state' => 'Dubai',
                    'org_country_id' => 1,
                    'org_phone' => '+971-4-8800000',
                    'org_currency' => 'AED',
                    'gstin_number' => 'GST001',
                    'gst_reg_date' => date('Y-m-d'),
                    'org_status' => true,
                ]
            );
            $organisations = collect([$org]);
        }

        foreach ($organisations as $org) {
            // Helper to find area by code
            $getAreaId = fn (string $code) => Area::where('organisation_id', $org->id)->where('area_code', $code)->first()?->id ?? Area::first()->id;

            // ─────────────────────────────────────────────────────────────
            // DUBAI BEATS
            // ─────────────────────────────────────────────────────────────
            $dubaiBeats = [
                ['beat_code' => 'BEAT-DXB-01', 'beat_name' => 'Al Ras Wholesale Beat',        'area_code' => 'AREA-DXB-01'],
                ['beat_code' => 'BEAT-DXB-02', 'beat_name' => 'Naif Market Grocery Beat',     'area_code' => 'AREA-DXB-01'],
                ['beat_code' => 'BEAT-DXB-03', 'beat_name' => 'Karama Commercial Beat',       'area_code' => 'AREA-DXB-02'],
                ['beat_code' => 'BEAT-DXB-04', 'beat_name' => 'Meena Bazaar Retail Beat',      'area_code' => 'AREA-DXB-02'],
                ['beat_code' => 'BEAT-DXB-05', 'beat_name' => 'Business Bay Towers Beat',     'area_code' => 'AREA-DXB-03'],
                ['beat_code' => 'BEAT-DXB-06', 'beat_name' => 'Al Barsha 1 Mart Beat',        'area_code' => 'AREA-DXB-04'],
                ['beat_code' => 'BEAT-DXB-07', 'beat_name' => 'Marina Walk Retail Beat',      'area_code' => 'AREA-DXB-05'],
                ['beat_code' => 'BEAT-DXB-08', 'beat_name' => 'DIP Industrial Stores Beat',   'area_code' => 'AREA-DXB-06'],
            ];

            foreach ($dubaiBeats as $b) {
                Beat::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'beat_code' => $b['beat_code'],
                    ],
                    [
                        'area_id' => $getAreaId($b['area_code']),
                        'beat_name' => $b['beat_name'],
                        'status' => true,
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────
            // INDIA BEATS
            // ─────────────────────────────────────────────────────────────
            $indiaBeats = [
                ['beat_code' => 'BEAT-IND-01', 'beat_name' => 'Andheri West Link Road Beat',  'area_code' => 'AREA-IND-01'],
                ['beat_code' => 'BEAT-IND-02', 'beat_name' => 'Bandra Linking Road Beat',     'area_code' => 'AREA-IND-01'],
                ['beat_code' => 'BEAT-IND-03', 'beat_name' => 'Ghatkopar Station Market Beat','area_code' => 'AREA-IND-02'],
                ['beat_code' => 'BEAT-IND-04', 'beat_name' => 'Thane Gokhale Road Beat',       'area_code' => 'AREA-IND-03'],
                ['beat_code' => 'BEAT-IND-05', 'beat_name' => 'Vashi Sector 17 Beat',         'area_code' => 'AREA-IND-03'],
                ['beat_code' => 'BEAT-IND-06', 'beat_name' => 'FC Road & Deccan Beat',        'area_code' => 'AREA-IND-04'],
                ['beat_code' => 'BEAT-IND-07', 'beat_name' => 'Lajpat Nagar Central Beat',    'area_code' => 'AREA-IND-05'],
                ['beat_code' => 'BEAT-IND-08', 'beat_name' => 'Koramangala 80ft Road Beat',   'area_code' => 'AREA-IND-06'],
            ];

            foreach ($indiaBeats as $b) {
                Beat::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'beat_code' => $b['beat_code'],
                    ],
                    [
                        'area_id' => $getAreaId($b['area_code']),
                        'beat_name' => $b['beat_name'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}
