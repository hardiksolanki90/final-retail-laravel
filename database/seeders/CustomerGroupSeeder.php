<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $groups = [
            ['group_code' => 'GRP001', 'group_name' => 'Key Accounts', 'type' => 'premium'],
            ['group_code' => 'GRP002', 'group_name' => 'Standard', 'type' => 'standard'],
            ['group_code' => 'GRP003', 'group_name' => 'New Customers', 'type' => 'new'],
            ['group_code' => 'GRP004', 'group_name' => 'Inactive', 'type' => 'inactive'],
            ['group_code' => 'GRP005', 'group_name' => 'VIP', 'type' => 'vip'],
        ];

        foreach ($organisations as $org) {
            foreach ($groups as $group) {
                CustomerGroup::create(array_merge($group, [
                    'uuid' => fake()->uuid(),
                    'organisation_id' => $org->id,
                    'status' => true,
                ]));
            }
        }
    }
}