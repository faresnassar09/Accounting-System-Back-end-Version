<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Modules\Authorization\Models\Role;
use Modules\Branch\Models\Branch;
use Modules\User\Models\User;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant', 'guard_name' => 'web']);
    $this->user->assignRole($role);

    Passport::actingAs($this->user);
});

test('user can list active branches', function () {
    $b1 = Branch::create(['name' => 'Cairo Branch', 'code' => 'CAI', 'phone' => '0100000001', 'address' => 'Cairo', 'active' => 1]);
    $b2 = Branch::create(['name' => 'Alex Branch', 'code' => 'ALX', 'phone' => '0100000002', 'address' => 'Alexandria', 'active' => 1]);
    $b3 = Branch::create(['name' => 'Inactive Branch', 'code' => 'INA', 'phone' => '0100000003', 'address' => 'Giza', 'active' => 0]);

    $response = $this->getJson('/api/v1/branches');
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $data = $response->json('data');
    expect($data)->toHaveCount(2);
    expect(collect($data)->pluck('code')->toArray())->toContain('CAI', 'ALX');
    expect(collect($data)->pluck('code')->toArray())->not->toContain('INA');
});

test('user can view a single branch', function () {
    $branch = Branch::create(['name' => 'Main Office', 'code' => 'HQ', 'phone' => '0100000009', 'address' => 'Downtown', 'active' => 1]);

    $response = $this->getJson("/api/v1/branches/{$branch->id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.code', 'HQ');
});

test('returns 404 for non-existent branch', function () {
    $response = $this->getJson('/api/v1/branches/9999');
    $response->assertStatus(404);
    $response->assertJsonPath('success', false);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
