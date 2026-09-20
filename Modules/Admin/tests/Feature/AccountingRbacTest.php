<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Admin\Filament\Pages\FinancialClosing\FinancialClosingPage;
use Modules\Admin\Filament\Resources\Budgets\BudgetResource;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('super_admin has full access across closing, budgets, and journal entries', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin, 'admin');

    expect(FinancialClosingPage::canAccess())->toBeTrue();
    expect(JournalEntryResource::canCreate())->toBeTrue();
    expect(BudgetResource::canCreate())->toBeTrue();
});

test('finance_manager can access financial closing, budgets, and create entries', function () {
    $admin = Admin::factory()->create();
    $role = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'admin']);
    $admin->assignRole($role);
    $this->actingAs($admin, 'admin');

    expect(FinancialClosingPage::canAccess())->toBeTrue();
    expect(JournalEntryResource::canCreate())->toBeTrue();
    expect(BudgetResource::canCreate())->toBeTrue();
});

test('accountant can create journal entries but cannot access closing or manage budgets', function () {
    $admin = Admin::factory()->create();
    $role = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'admin']);
    $admin->assignRole($role);
    $this->actingAs($admin, 'admin');

    expect(FinancialClosingPage::canAccess())->toBeFalse();
    expect(JournalEntryResource::canCreate())->toBeTrue();
    expect(BudgetResource::canCreate())->toBeFalse();
});

test('auditor has read-only access and is blocked from creating entries, closing, or budgets', function () {
    $admin = Admin::factory()->create();
    $role = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'admin']);
    $admin->assignRole($role);
    $this->actingAs($admin, 'admin');

    expect(FinancialClosingPage::canAccess())->toBeFalse();
    expect(JournalEntryResource::canCreate())->toBeFalse();
    expect(BudgetResource::canCreate())->toBeFalse();
});
