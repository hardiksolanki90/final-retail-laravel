<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Organisation;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegionSeeder extends Seeder
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
            // Ensure Countries exist
            $uae = Country::firstOrCreate(
                ['organisation_id' => $org->id, 'country_code' => 'UAE'],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => 'United Arab Emirates',
                    'dial_code' => '+971',
                    'currency' => 'AED',
                    'currency_code' => 'AED',
                    'currency_symbol' => 'AED',
                    'status' => true,
                ]
            );

            $india = Country::firstOrCreate(
                ['organisation_id' => $org->id, 'country_code' => 'IND'],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => 'India',
                    'dial_code' => '+91',
                    'currency' => 'INR',
                    'currency_code' => 'INR',
                    'currency_symbol' => '₹',
                    'status' => true,
                ]
            );

            // Dubai Regions
            $dubaiRegions = [
                ['region_code' => 'REG-DXB-01', 'region_name' => 'Dubai North (Deira & Al Qusais)'],
                ['region_code' => 'REG-DXB-02', 'region_name' => 'Dubai Central (Bur Dubai & Downtown)'],
                ['region_code' => 'REG-DXB-03', 'region_name' => 'New Dubai (Marina, JLT & Palm)'],
                ['region_code' => 'REG-DXB-04', 'region_name' => 'Dubai South & Industrial (DIP & JAFZA)'],
            ];

            foreach ($dubaiRegions as $r) {
                Region::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'region_code' => $r['region_code'],
                    ],
                    [
                        'country_id' => $uae->id,
                        'region_name' => $r['region_name'],
                        'region_status' => true,
                    ]
                );
            }

            // India Regions
            $indiaRegions = [
                ['region_code' => 'REG-IND-01', 'region_name' => 'West Zone (Mumbai & Maharashtra)'],
                ['region_code' => 'REG-IND-02', 'region_name' => 'North Zone (Delhi NCR)'],
                ['region_code' => 'REG-IND-03', 'region_name' => 'South Zone (Bengaluru & Karnataka)'],
            ];

            foreach ($indiaRegions as $r) {
                Region::updateOrCreate(
                    [
                        'organisation_id' => $org->id,
                        'region_code' => $r['region_code'],
                    ],
                    [
                        'country_id' => $india->id,
                        'region_name' => $r['region_name'],
                        'region_status' => true,
                    ]
                );
            }
        }
    }
}
