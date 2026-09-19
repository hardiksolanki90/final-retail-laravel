<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\CustomerCategory;
use App\Models\CustomerGroup;
use App\Models\Channel;
use App\Models\PaymentTerm;
use App\Models\Route;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        if ($organisations->isEmpty()) {
            return;
        }

        $customerTypes = CustomerType::all();
        $customerCategories = CustomerCategory::all();
        $customerGroups = CustomerGroup::all();
        $channels = Channel::all();
        $paymentTerms = PaymentTerm::all();
        $routes = Route::all();
        // $salesmen = User::whereHas('roles', fn($q) => $q->where('name', 'salesman'))->get();

        // if ($salesmen->isEmpty()) {
        //     $salesmen = User::all();
        // }

        $customers = [
            [
                'customer_code' => 'CUST001',
                'shop_name' => 'Metro Supermarket',
                'firstname' => 'Rajesh',
                'lastname' => 'Kumar',
                'email' => 'rajesh.kumar@metro.com',
                'phone' => '9876543210',
                'address' => '123 Main Street, Sector 15',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zipcode' => '400001',
                'latitude' => 19.0760,
                'longitude' => 72.8777,
                'balance' => 15000.00,
                'credit_limit' => 50000.00,
                'credit_days' => 30,
                'trn_no' => 'TRN123456',
                'profile_image' => null,
            ],
            [
                'customer_code' => 'CUST002',
                'shop_name' => 'Reliance Fresh',
                'firstname' => 'Priya',
                'lastname' => 'Sharma',
                'email' => 'priya.sharma@reliance.com',
                'phone' => '9876543211',
                'address' => '456 Park Avenue, Andheri West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zipcode' => '400058',
                'latitude' => 19.1197,
                'longitude' => 72.8464,
                'balance' => 25000.00,
                'credit_limit' => 75000.00,
                'credit_days' => 45,
                'trn_no' => 'TRN123457',
                'profile_image' => null,
            ],
            [
                'customer_code' => 'CUST003',
                'shop_name' => 'Big Bazaar',
                'firstname' => 'Amit',
                'lastname' => 'Patel',
                'email' => 'amit.patel@bigbazaar.com',
                'phone' => '9876543212',
                'address' => '789 Linking Road, Bandra West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zipcode' => '400050',
                'latitude' => 19.0596,
                'longitude' => 72.8295,
                'balance' => 5000.00,
                'credit_limit' => 100000.00,
                'credit_days' => 30,
                'trn_no' => 'TRN123458',
                'profile_image' => null,
            ],
            [
                'customer_code' => 'CUST004',
                'shop_name' => 'DMart',
                'firstname' => 'Sunita',
                'lastname' => 'Singh',
                'email' => 'sunita.singh@dmart.com',
                'phone' => '9876543213',
                'address' => '321 SV Road, Malad West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zipcode' => '400064',
                'latitude' => 19.1885,
                'longitude' => 72.8489,
                'balance' => 0.00,
                'credit_limit' => 50000.00,
                'credit_days' => 15,
                'trn_no' => 'TRN123459',
                'profile_image' => null,
            ],
            [
                'customer_code' => 'CUST005',
                'shop_name' => 'Spencer\'s Retail',
                'firstname' => 'Vikram',
                'lastname' => 'Mehta',
                'email' => 'vikram.mehta@spencers.com',
                'phone' => '9876543214',
                'address' => '555 Hill Road, Bandra West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zipcode' => '400050',
                'latitude' => 19.0544,
                'longitude' => 72.8321,
                'balance' => 30000.00,
                'credit_limit' => 60000.00,
                'credit_days' => 30,
                'trn_no' => 'TRN123460',
                'profile_image' => null,
            ],
        ];

        // foreach ($organisations as $org) {
        //     foreach ($customers as $index => $customer) {
        //         Customer::create(array_merge($customer, [
        //             'uuid' => fake()->uuid(),
        //             'organisation_id' => $org->id,
        //             'customer_type_id' => $customerTypes->random()?->id,
        //             'customer_category_id' => $customerCategories->where('organisation_id', $org->id)->random()?->id,
        //             'customer_group_id' => $customerGroups->where('organisation_id', $org->id)->random()?->id,
        //             'channel_id' => $channels->where('organisation_id', $org->id)->random()?->id,
        //             'payment_term_id' => $paymentTerms->where('organisation_id', $org->id)->random()?->id,
        //             'route_id' => $routes->where('organisation_id', $org->id)->random()?->id,
        //             'salesman_id' => $salesmen->random()?->id,
        //             'status' => true,
        //         ]));
        //     }

        //     for ($i = 6; $i <= 25; $i++) {
        //         Customer::create([
        //             'uuid' => fake()->uuid(),
        //             'organisation_id' => $org->id,
        //             'customer_code' => 'CUST' . str_pad($i, 3, '0', STR_PAD_LEFT),
        //             'shop_name' => fake()->company() . ' ' . fake()->randomElement(['Store', 'Shop', 'Mart', 'Retail']),
        //             'firstname' => fake()->firstName(),
        //             'lastname' => fake()->lastName(),
        //             'email' => fake()->unique()->safeEmail(),
        //             'phone' => fake()->unique()->numerify('9##########'),
        //             'address' => fake()->streetAddress(),
        //             'city' => fake()->city(),
        //             'state' => fake()->state(),
        //             'zipcode' => fake()->postcode(),
        //             'latitude' => fake()->latitude(18.9, 19.3),
        //             'longitude' => fake()->longitude(72.7, 73.0),
        //             'balance' => fake()->randomFloat(2, 0, 100000),
        //             'credit_limit' => fake()->randomFloat(2, 10000, 200000),
        //             'credit_days' => fake()->randomElement([0, 7, 15, 30, 45, 60]),
        //             'trn_no' => 'TRN' . fake()->unique()->numerify('######'),
        //             'profile_image' => null,
        //             'customer_type_id' => $customerTypes->random()?->id,
        //             'customer_category_id' => $customerCategories->where('organisation_id', $org->id)->random()?->id,
        //             'customer_group_id' => $customerGroups->where('organisation_id', $org->id)->random()?->id,
        //             'channel_id' => $channels->where('organisation_id', $org->id)->random()?->id,
        //             'payment_term_id' => $paymentTerms->where('organisation_id', $org->id)->random()?->id,
        //             'route_id' => $routes->where('organisation_id', $org->id)->random()?->id,
        //             'salesman_id' => $salesmen->random()?->id,
        //             'status' => fake()->boolean(90),
        //         ]);
        //     }
        // }
    }
}
