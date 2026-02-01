
<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Models\User;
use App\Models\Tenant;

uses(TestCase::class, RefreshDatabase::class);

test('allows a user to login with correct credentials', function () {
    $tenant = Tenant::create();
    $tenant->domains()->create(['domain' => 'tenant1.app.test']);
    tenancy()->initialize($tenant);

    $user = User::factory()->create([
        'password' => bcrypt('00000000')
    ]);

    $response = $this->post('api/v1/auth/login', [
        'email' => $user->email,
        'password' => '00000000',
    ]);

    $response->assertStatus(200); 
    $this->assertAuthenticatedAs($user); 
    
});

