<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GeoHierarchySeeder extends Seeder
{
    /**
     * Run the geographic & territorial hierarchy seeds.
     * Execution Order:
     * 1. RegionSeeder (Countries & Regions for UAE & India)
     * 2. AreaSeeder (Parent & Sub-areas)
     * 3. DepotSeeder (Distribution Depots linked to Regions & Areas)
     * 4. BeatSeeder (Sales Beats linked to Areas)
     * 5. RouteSeeder (Delivery Routes linked to Areas, Depots, and Beats)
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            AreaSeeder::class,
            DepotSeeder::class,
            BeatSeeder::class,
            RouteSeeder::class,
        ]);
    }
}
