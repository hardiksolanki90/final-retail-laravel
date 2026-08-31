<?php

namespace Database\Seeders;

use App\Models\CustomerCategory;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class CustomerCategorySeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $categories = [
            ['customer_category_code' => 'CC01', 'customer_category_name' => 'Agent'],
            ['customer_category_code' => 'CC02', 'customer_category_name' => 'Depo'],
            ['customer_category_code' => 'CC03', 'customer_category_name' => 'Distributor'],
            ['customer_category_code' => 'CC04', 'customer_category_name' => 'Wholesaler'],
            ['customer_category_code' => 'CC05', 'customer_category_name' => 'Retailer'],
            ['customer_category_code' => 'CC06', 'customer_category_name' => 'Super Stockist'],
            ['customer_category_code' => 'CC07', 'customer_category_name' => 'Sub Stockist'],
        ];

        foreach ($organisations as $org) {
            $parent = null;
            foreach ($categories as $index => $cat) {
                $created = CustomerCategory::create(array_merge($cat, [
                    'uuid' => fake()->uuid(),
                    'organisation_id' => $org->id,
                    'parent_id' => $parent?->id,
                    'node_level' => $parent ? $parent->node_level + 1 : 0,
                    'status' => true,
                ]));
                if ($index === 0) {
                    $parent = $created;
                }
            }
        }
    }
}