<?php

namespace Modules\User\tests\Feature\Auth;

use App\Models\Tenant;
use Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(){

        $tenant = Tenant::create();
         $tenant->domains()->create(['domain' => 'tenant1.app.test']);

        tenancy()->initialize($tenant);

        $user = User::factory()->create([

            'password' => Hash::make('00000000')
        ]);
        $response = $this->post('api/v1/auth/login',[

            'email' => $user->email,
            'password' => '00000000',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertStatus(200);

    }
}
