<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Depot;
use App\Models\Organisation;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepotSeeder extends Seeder
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
            // Fetch Regions
            $regDxbNorth   = Region::where('organisation_id', $org->id)->where('region_code', 'REG-DXB-01')->first();
            $regDxbCentral = Region::where('organisation_id', $org->id)->where('region_code', 'REG-DXB-02')->first();
            $regDxbSouth   = Region::where('organisation_id', $org->id)->where('region_code', 'REG-DXB-04')->first();

            $regIndWest    = Region::where('organisation_id', $org->id)->where('region_code', 'REG-IND-01')->first();
            $regIndNorth   = Region::where('organisation_id', $org->id)->where('region_code', 'REG-IND-02')->first();
            $regIndSouth   = Region::where('organisation_id', $org->id)->where('region_code', 'REG-IND-03')->first();

            // Fetch Areas
            $areaDeira     = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-DXB-01')->first();
            $areaAlQuoz    = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-DXB-03')->first();
            $areaDip       = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-DXB-06')->first();

            $areaMumbEast  = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-IND-02')->first();
            $areaThane     = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-IND-03')->first();
            $areaPune      = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-IND-04')->first();
            $areaDelhi     = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-IND-05')->first();
            $areaBlr       = Area::where('organisation_id', $org->id)->where('area_code', 'AREA-IND-06')->first();

            // ─────────────────────────────────────────────────────────────
            // DUBAI DEPOTS
            // ─────────────────────────────────────────────────────────────
            $dubaiDepots = [
                [
                    'depot_code' => 'DEP-DXB-01',
                    'depot_name' => 'Al Quoz Central Distribution Depot',
                    'region_id'  => $regDxbCentral?->id ?? Region::first()->id,
                    'area_id'    => $areaAlQuoz?->id,
                    'depot_manager' => 'Rashid Al Mansoori',
                    'depot_manager_contact' => '+971-50-1234567',
                ],
                [
                    'depot_code' => 'DEP-DXB-02',
                    'depot_name' => 'Deira Port Saeed Depot',
                    'region_id'  => $regDxbNorth?->id ?? Region::first()->id,
                    'area_id'    => $areaDeira?->id,
                    'depot_manager' => 'Tariq Mehmood',
                    'depot_manager_contact' => '+971-50-2345678',
                ],
                [
                    'depot_code' => 'DEP-DXB-03',
                    'depot_name' => 'DIP Logistics Park Depot',
                    'region_id'  => $regDxbSouth?->id ?? Region::first()->id,
                    'area_id'    => $areaDip?->id,
                    'depot_manager' => 'Ahmed Al Suwaidi',
                    'depot_manager_contact' => '+971-50-3456789',
                ],
            ];

            foreach ($dubaiDepots as $d) {
                Depot::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'depot_code' => $d['depot_code'],
                    ],
                    [
                        'region_id' => $d['region_id'],
                        'area_id' => $d['area_id'],
                        'depot_name' => $d['depot_name'],
                        'depot_manager' => $d['depot_manager'],
                        'depot_manager_contact' => $d['depot_manager_contact'],
                        'status' => true,
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────
            // INDIA DEPOTS
            // ─────────────────────────────────────────────────────────────
            $indiaDepots = [
                [
                    'depot_code' => 'DEP-IND-01',
                    'depot_name' => 'Bhiwandi Central Depot',
                    'region_id'  => $regIndWest?->id ?? Region::first()->id,
                    'area_id'    => $areaMumbEast?->id,
                    'depot_manager' => 'Rajesh Sharma',
                    'depot_manager_contact' => '+91-9820011223',
                ],
                [
                    'depot_code' => 'DEP-IND-02',
                    'depot_name' => 'Taloja MIDC Depot',
                    'region_id'  => $regIndWest?->id ?? Region::first()->id,
                    'area_id'    => $areaThane?->id,
                    'depot_manager' => 'Suresh Patil',
                    'depot_manager_contact' => '+91-9820033445',
                ],
                [
                    'depot_code' => 'DEP-IND-03',
                    'depot_name' => 'Bhosari Pune Depot',
                    'region_id'  => $regIndWest?->id ?? Region::first()->id,
                    'area_id'    => $areaPune?->id,
                    'depot_manager' => 'Amit Deshmukh',
                    'depot_manager_contact' => '+91-9820055667',
                ],
                [
                    'depot_code' => 'DEP-IND-04',
                    'depot_name' => 'Okhla Industrial Depot',
                    'region_id'  => $regIndNorth?->id ?? Region::first()->id,
                    'area_id'    => $areaDelhi?->id,
                    'depot_manager' => 'Vikram Malhotra',
                    'depot_manager_contact' => '+91-9810077889',
                ],
                [
                    'depot_code' => 'DEP-IND-05',
                    'depot_name' => 'Whitefield Bengaluru Depot',
                    'region_id'  => $regIndSouth?->id ?? Region::first()->id,
                    'area_id'    => $areaBlr?->id,
                    'depot_manager' => 'Karthik Rao',
                    'depot_manager_contact' => '+91-9845099001',
                ],
            ];

            foreach ($indiaDepots as $d) {
                Depot::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'depot_code' => $d['depot_code'],
                    ],
                    [
                        'region_id' => $d['region_id'],
                        'area_id' => $d['area_id'],
                        'depot_name' => $d['depot_name'],
                        'depot_manager' => $d['depot_manager'],
                        'depot_manager_contact' => $d['depot_manager_contact'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}
