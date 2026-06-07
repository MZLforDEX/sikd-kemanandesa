<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles for welcome page statistics
        Role::create(['name' => 'warga', 'display_name' => 'Warga']);
        Role::create(['name' => 'perangkat', 'display_name' => 'Perangkat']);
        Role::create(['name' => 'satpam', 'display_name' => 'Satpam']);
        Role::create(['name' => 'kades', 'display_name' => 'Kades']);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
