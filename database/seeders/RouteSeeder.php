<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $routes = [
            ['route_code' => 'RT001', 'route_name' => 'North Route - Sector 1'],
            ['route_code' => 'RT002', 'route_name' => 'North Route - Sector 2'],
            ['route_code' => 'RT003', 'route_name' => 'South Route - Sector 1'],
            ['route_code' => 'RT004', 'route_name' => 'South Route - Sector 2'],
            ['route_code' => 'RT005', 'route_name' => 'East Route - Sector 1'],
            ['route_code' => 'RT006', 'route_name' => 'East Route - Sector 2'],
            ['route_code' => 'RT007', 'route_name' => 'West Route - Sector 1'],
            ['route_code' => 'RT008', 'route_name' => 'West Route - Sector 2'],
            ['route_code' => 'RT009', 'route_name' => 'Central Route - Main'],
            ['route_code' => 'RT010', 'route_name' => 'Central Route - Extension'],
        ];

        foreach ($organisations as $org) {
            foreach ($routes as $index => $route) {
                Route::create(array_merge($route, [
                    'uuid' => fake()->uuid(),
                    'organisation_id' => $org->id,
                    'area_id' => ($index % 4) + 1,
                    'depot_id' => ($index % 3) + 1,
                    'van_id' => ($index % 5) + 1,
                    'status' => true,
                ]));
            }
        }
    }
}