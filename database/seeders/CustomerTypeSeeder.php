<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['customer_type_code' => 'HO', 'customer_type_name' => 'Head Office'],
            ['customer_type_code' => 'BR', 'customer_type_name' => 'Branch'],
            ['customer_type_code' => 'NR', 'customer_type_name' => 'Normal'],
            ['customer_type_code' => 'WH', 'customer_type_name' => 'Warehouse'],
            ['customer_type_code' => 'DP', 'customer_type_name' => 'Depot'],
        ];

        foreach ($types as $type) {
            CustomerType::create(array_merge($type, [
                'uuid' => fake()->uuid(),
                'status' => true,
            ]));
        }
    }
}