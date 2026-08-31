<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class PaymentTermSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $terms = [
            ['name' => 'Immediate', 'payment_code' => 'IMM', 'number_of_days' => 0],
            ['name' => 'Net 7 Days', 'payment_code' => 'NET7', 'number_of_days' => 7],
            ['name' => 'Net 15 Days', 'payment_code' => 'NET15', 'number_of_days' => 15],
            ['name' => 'Net 30 Days', 'payment_code' => 'NET30', 'number_of_days' => 30],
            ['name' => 'Net 45 Days', 'payment_code' => 'NET45', 'number_of_days' => 45],
            ['name' => 'Net 60 Days', 'payment_code' => 'NET60', 'number_of_days' => 60],
            ['name' => 'Net 90 Days', 'payment_code' => 'NET90', 'number_of_days' => 90],
            ['name' => 'End of Month', 'payment_code' => 'EOM', 'number_of_days' => 30],
            ['name' => 'Cash on Delivery', 'payment_code' => 'COD', 'number_of_days' => 0],
            ['name' => 'Advance Payment', 'payment_code' => 'ADV', 'number_of_days' => 0],
        ];

        foreach ($organisations as $org) {
            foreach ($terms as $term) {
                PaymentTerm::create(array_merge($term, [
                    'uuid' => fake()->uuid(),
                    'organisation_id' => $org->id,
                    'status' => true,
                ]));
            }
        }
    }
}