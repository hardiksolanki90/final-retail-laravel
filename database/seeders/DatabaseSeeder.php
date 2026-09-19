<?php

namespace Database\Seeders;

use App\Models\CountryMaster;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Schema::hasTable('country_masters')) {
            DB::table('country_masters')->delete();
            DB::unprepared(file_get_contents(storage_path('backups/country_masters.sql')));
        }

        $this->call([
            CurrencyMasterSeeder::class,
            CustomerTypeSeeder::class,
            CustomerCategorySeeder::class,
            CustomerGroupSeeder::class,
            ChannelSeeder::class,
            PaymentTermSeeder::class,
            RegionSeeder::class,
            AreaSeeder::class,
            DepotSeeder::class,
            BeatSeeder::class,
            RouteSeeder::class,
            CustomerSeeder::class,

        ]);

        DB::table('users')->delete();
        $superAdminUser = new User();
        $superAdminUser->uuid                    = Str::uuid();
        $superAdminUser->usertype              = 0;
        $superAdminUser->firstname             = 'Rud';
        $superAdminUser->lastname              = 'Super Admin';
        $superAdminUser->email                 = 'superadmin@admin.com';
        $superAdminUser->email_verified_at     = date('Y-m-d H:i:s');
        $superAdminUser->password              = Hash::make('123456');
        $superAdminUser->country_id           = CountryMaster::whereName('India')->first()->id;
        $superAdminUser->mobile               = '9033777859';
        $superAdminUser->api_token            = Str::random(35);
        $superAdminUser->role_id               = 1;
        $superAdminUser->save();
    }
}
