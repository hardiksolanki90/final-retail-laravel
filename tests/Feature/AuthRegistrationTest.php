<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_minimal_details_and_create_organisation(): void
    {
        $payload = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@acme.test',
            'mobile' => '9876543210',
            'org_name' => 'Acme Supermarket',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.firstname', 'John')
            ->assertJsonPath('data.user.email', 'john@acme.test')
            ->assertJsonPath('data.user.organisation.org_name', 'Acme Supermarket');

        $this->assertDatabaseHas('organisations', [
            'org_name' => 'Acme Supermarket',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@acme.test',
            'firstname' => 'John',
            'usertype' => 1,
        ]);
    }

    public function test_registration_fails_if_organisation_name_is_missing(): void
    {
        $payload = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane@acme.test',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['org_name']);
    }

    public function test_authenticated_user_can_retrieve_current_organisation(): void
    {
        $org = Organisation::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reg_software_id' => 1,
            'org_name' => 'Global Retailers',
            'org_company_id' => 'GLOB-12345',
            'org_street1' => '100 Market St',
            'org_country_id' => 1,
            'org_phone' => '1234567890',
            'gstin_number' => '',
            'gst_reg_date' => '',
        ]);

        $user = User::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organisation_id' => $org->id,
            'usertype' => 1,
            'firstname' => 'Owner',
            'email' => 'owner@global.test',
            'password' => 'password',
            'api_token' => \Illuminate\Support\Str::random(60),
            'login_type' => 'system',
        ]);

        $response = $this->actingAs($user)->getJson('/api/organisation/current');

        $response->assertStatus(200)
            ->assertJsonPath('data.org_name', 'Global Retailers')
            ->assertJsonPath('data.org_company_id', 'GLOB-12345');
    }
}
