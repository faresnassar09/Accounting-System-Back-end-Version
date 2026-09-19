<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Modules\Authorization\Models\Role;
use Modules\Teams\Models\Team;
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

test('user can list active teams', function () {
    $t1 = Team::create(['name' => 'Finance Team', 'active' => 1]);
    $t2 = Team::create(['name' => 'Sales Team', 'active' => 1]);
    $t3 = Team::create(['name' => 'Disbanded Team', 'active' => 0]);

    $response = $this->getJson('/api/v1/teams');
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $data = $response->json('data');
    expect($data)->toHaveCount(2);
    expect(collect($data)->pluck('name')->toArray())->toContain('Finance Team', 'Sales Team');
    expect(collect($data)->pluck('name')->toArray())->not->toContain('Disbanded Team');
});

test('user can view a single team', function () {
    $team = Team::create(['name' => 'Audit Team', 'active' => 1]);

    $response = $this->getJson("/api/v1/teams/{$team->id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.name', 'Audit Team');
});

test('returns 404 for non-existent team', function () {
    $response = $this->getJson('/api/v1/teams/9999');
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
