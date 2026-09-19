<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Beat;
use App\Models\Depot;
use App\Models\Organisation;
use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RouteSeeder extends Seeder
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
            // Helpers to find foreign keys by code
            $getAreaId = fn (string $code) => Area::where('organisation_id', $org->id)->where('area_code', $code)->value('id') ?? Area::first()?->id;
            $getDepotId = fn (string $code) => Depot::where('organisation_id', $org->id)->where('depot_code', $code)->value('id') ?? Depot::first()?->id;
            $getBeatId = fn (string $code) => Beat::where('organisation_id', $org->id)->where('beat_code', $code)->value('id') ?? Beat::first()?->id;

            // ─────────────────────────────────────────────────────────────
            // DUBAI ROUTES
            // ─────────────────────────────────────────────────────────────
            $dubaiRoutes = [
                [
                    'route_code' => 'RT-DXB-001',
                    'route_name' => 'Deira Wholesale FMCG Route',
                    'area_code'  => 'AREA-DXB-01',
                    'depot_code' => 'DEP-DXB-02',
                    'beat_code'  => 'BEAT-DXB-01',
                ],
                [
                    'route_code' => 'RT-DXB-002',
                    'route_name' => 'Naif Traditional Trade Route',
                    'area_code'  => 'AREA-DXB-01',
                    'depot_code' => 'DEP-DXB-02',
                    'beat_code'  => 'BEAT-DXB-02',
                ],
                [
                    'route_code' => 'RT-DXB-003',
                    'route_name' => 'Karama Daily Groceries Route',
                    'area_code'  => 'AREA-DXB-02',
                    'depot_code' => 'DEP-DXB-01',
                    'beat_code'  => 'BEAT-DXB-03',
                ],
                [
                    'route_code' => 'RT-DXB-004',
                    'route_name' => 'Meena Bazaar Supermarket Route',
                    'area_code'  => 'AREA-DXB-02',
                    'depot_code' => 'DEP-DXB-01',
                    'beat_code'  => 'BEAT-DXB-04',
                ],
                [
                    'route_code' => 'RT-DXB-005',
                    'route_name' => 'Business Bay Key Accounts Route',
                    'area_code'  => 'AREA-DXB-03',
                    'depot_code' => 'DEP-DXB-01',
                    'beat_code'  => 'BEAT-DXB-05',
                ],
                [
                    'route_code' => 'RT-DXB-006',
                    'route_name' => 'Al Barsha Retail Route',
                    'area_code'  => 'AREA-DXB-04',
                    'depot_code' => 'DEP-DXB-01',
                    'beat_code'  => 'BEAT-DXB-06',
                ],
                [
                    'route_code' => 'RT-DXB-007',
                    'route_name' => 'Marina & JLT Modern Trade Route',
                    'area_code'  => 'AREA-DXB-05',
                    'depot_code' => 'DEP-DXB-03',
                    'beat_code'  => 'BEAT-DXB-07',
                ],
                [
                    'route_code' => 'RT-DXB-008',
                    'route_name' => 'DIP Logistics Van Route',
                    'area_code'  => 'AREA-DXB-06',
                    'depot_code' => 'DEP-DXB-03',
                    'beat_code'  => 'BEAT-DXB-08',
                ],
            ];

            foreach ($dubaiRoutes as $r) {
                Route::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'route_code' => $r['route_code'],
                    ],
                    [
                        'route_name' => $r['route_name'],
                        'area_id' => $getAreaId($r['area_code']),
                        'depot_id' => $getDepotId($r['depot_code']),
                        'beat_id' => $getBeatId($r['beat_code']),
                        'status' => true,
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────
            // INDIA ROUTES
            // ─────────────────────────────────────────────────────────────
            $indiaRoutes = [
                [
                    'route_code' => 'RT-IND-001',
                    'route_name' => 'Andheri Suburban Van Route',
                    'area_code'  => 'AREA-IND-01',
                    'depot_code' => 'DEP-IND-01',
                    'beat_code'  => 'BEAT-IND-01',
                ],
                [
                    'route_code' => 'RT-IND-002',
                    'route_name' => 'Bandra Khar Retail Route',
                    'area_code'  => 'AREA-IND-01',
                    'depot_code' => 'DEP-IND-01',
                    'beat_code'  => 'BEAT-IND-02',
                ],
                [
                    'route_code' => 'RT-IND-003',
                    'route_name' => 'Ghatkopar East FMCG Route',
                    'area_code'  => 'AREA-IND-02',
                    'depot_code' => 'DEP-IND-01',
                    'beat_code'  => 'BEAT-IND-03',
                ],
                [
                    'route_code' => 'RT-IND-004',
                    'route_name' => 'Thane City Main Route',
                    'area_code'  => 'AREA-IND-03',
                    'depot_code' => 'DEP-IND-02',
                    'beat_code'  => 'BEAT-IND-04',
                ],
                [
                    'route_code' => 'RT-IND-005',
                    'route_name' => 'Vashi Commercial Route',
                    'area_code'  => 'AREA-IND-03',
                    'depot_code' => 'DEP-IND-02',
                    'beat_code'  => 'BEAT-IND-05',
                ],
                [
                    'route_code' => 'RT-IND-006',
                    'route_name' => 'Pune Deccan & Kothrud Route',
                    'area_code'  => 'AREA-IND-04',
                    'depot_code' => 'DEP-IND-03',
                    'beat_code'  => 'BEAT-IND-06',
                ],
                [
                    'route_code' => 'RT-IND-007',
                    'route_name' => 'Delhi South Retail Express',
                    'area_code'  => 'AREA-IND-05',
                    'depot_code' => 'DEP-IND-04',
                    'beat_code'  => 'BEAT-IND-07',
                ],
                [
                    'route_code' => 'RT-IND-008',
                    'route_name' => 'Bengaluru Tech Corridor Route',
                    'area_code'  => 'AREA-IND-06',
                    'depot_code' => 'DEP-IND-05',
                    'beat_code'  => 'BEAT-IND-08',
                ],
            ];

            foreach ($indiaRoutes as $r) {
                Route::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'route_code' => $r['route_code'],
                    ],
                    [
                        'route_name' => $r['route_name'],
                        'area_id' => $getAreaId($r['area_code']),
                        'depot_id' => $getDepotId($r['depot_code']),
                        'beat_id' => $getBeatId($r['beat_code']),
                        'status' => true,
                    ]
                );
            }
        }
    }
}