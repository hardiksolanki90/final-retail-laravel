<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Organisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
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
            // ─────────────────────────────────────────────────────────────
            // DUBAI AREAS
            // ─────────────────────────────────────────────────────────────
            $dubaiParent = Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-DXB-00',
                ],
                [
                    'area_name' => 'Dubai Metropolitan',
                    'parent_id' => null,
                    'node_level' => 0,
                    'status' => true,
                ]
            );

            $dubaiSubAreas = [
                ['area_code' => 'AREA-DXB-01', 'area_name' => 'Deira - Al Ras Wholesale'],
                ['area_code' => 'AREA-DXB-02', 'area_name' => 'Bur Dubai - Al Karama'],
                ['area_code' => 'AREA-DXB-03', 'area_name' => 'Al Quoz - Business Bay'],
                ['area_code' => 'AREA-DXB-04', 'area_name' => 'Al Barsha & Tecom'],
                ['area_code' => 'AREA-DXB-05', 'area_name' => 'Dubai Marina & JLT'],
                ['area_code' => 'AREA-DXB-06', 'area_name' => 'Dubai Investments Park (DIP)'],
            ];

            foreach ($dubaiSubAreas as $area) {
                Area::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'area_code' => $area['area_code'],
                    ],
                    [
                        'area_name' => $area['area_name'],
                        'parent_id' => $dubaiParent->id,
                        'node_level' => 1,
                        'status' => true,
                    ]
                );
            }

            // ─────────────────────────────────────────────────────────────
            // INDIA AREAS
            // ─────────────────────────────────────────────────────────────
            // 1. Maharashtra Parent & Sub-areas
            $mhParent = Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-IND-MH-00',
                ],
                [
                    'area_name' => 'Maharashtra State',
                    'parent_id' => null,
                    'node_level' => 0,
                    'status' => true,
                ]
            );

            $mhSubAreas = [
                ['area_code' => 'AREA-IND-01', 'area_name' => 'Mumbai Suburban West (Andheri - Bandra)'],
                ['area_code' => 'AREA-IND-02', 'area_name' => 'Mumbai Suburban East (Ghatkopar - Mulund)'],
                ['area_code' => 'AREA-IND-03', 'area_name' => 'Thane & Navi Mumbai'],
                ['area_code' => 'AREA-IND-04', 'area_name' => 'Pune Metro (Kothrud - Hinjewadi)'],
            ];

            foreach ($mhSubAreas as $area) {
                Area::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'area_code' => $area['area_code'],
                    ],
                    [
                        'area_name' => $area['area_name'],
                        'parent_id' => $mhParent->id,
                        'node_level' => 1,
                        'status' => true,
                    ]
                );
            }

            // 2. Delhi NCR Parent & Sub-areas
            $delhiParent = Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-IND-DL-00',
                ],
                [
                    'area_name' => 'Delhi NCR',
                    'parent_id' => null,
                    'node_level' => 0,
                    'status' => true,
                ]
            );

            Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-IND-05',
                ],
                [
                    'area_name' => 'Delhi South & Central',
                    'parent_id' => $delhiParent->id,
                    'node_level' => 1,
                    'status' => true,
                ]
            );

            // 3. Karnataka Parent & Sub-areas
            $kaParent = Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-IND-KA-00',
                ],
                [
                    'area_name' => 'Karnataka State',
                    'parent_id' => null,
                    'node_level' => 0,
                    'status' => true,
                ]
            );

            Area::updateOrCreate(
                [
                    'organisation_id' => $org->id,
                    'area_code' => 'AREA-IND-06',
                ],
                [
                    'area_name' => 'Bengaluru Central (Indiranagar - Koramangala)',
                    'parent_id' => $kaParent->id,
                    'node_level' => 1,
                    'status' => true,
                ]
            );
        }
    }
}
