<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\JournalEntry;
use Modules\Admin\Filament\Resources\Currencies\CurrencyResource;
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

test('tenant migration seeds initial standard currencies with USD as base', function () {
    expect(Currency::count())->toBeGreaterThanOrEqual(6);

    $base = Currency::getBaseCurrency();
    expect($base)->not->toBeNull();
    expect($base->code)->toBe('USD');
    expect((float) $base->exchange_rate)->toBe(1.0);
    expect($base->is_base)->toBeTrue();
    expect(Currency::getBaseCurrencyCode())->toBe('USD');
});

test('currency conversion helpers convert accurately between foreign and base currencies', function () {
    $eur = Currency::where('code', 'EUR')->first();
    expect($eur)->not->toBeNull();

    // 100 EUR with rate 1.08 = 108.0000 USD
    $convertedBase = $eur->convertToBase(100.0);
    expect($convertedBase)->toBe(108.0);

    // 108 USD converted back to EUR = 100.0000 EUR
    $convertedForeign = $eur->convertFromBase(108.0);
    expect($convertedForeign)->toBe(100.0);
});

test('journal entries support multi-currency attributes', function () {
    $entry = JournalEntry::create([
        'reference'     => 'JV-FX-001',
        'date'          => now(),
        'description'   => 'Euro equipment purchase',
        'type'          => 'journal',
        'status'        => 'approved',
        'total_debit'   => 1080.00,
        'total_credit'  => 1080.00,
        'currency_code' => 'EUR',
        'exchange_rate' => 1.080000,
    ]);

    expect($entry->currency_code)->toBe('EUR');
    expect((float) $entry->exchange_rate)->toBe(1.08);
    expect($entry->currency)->not->toBeNull();
    expect($entry->currency->code)->toBe('EUR');
});

test('currency resource enforces RBAC permissions for admin users', function () {
    // 1. Super Admin has access
    $superAdmin = Admin::factory()->create();
    $superAdmin->assignRole('super_admin');
    $this->actingAs($superAdmin, 'admin');
    expect(CurrencyResource::canAccess())->toBeTrue();
    expect(CurrencyResource::canCreate())->toBeTrue();

    // 2. Finance Manager has access
    $financeManager = Admin::factory()->create();
    $roleFM = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'admin']);
    $financeManager->assignRole($roleFM);
    $this->actingAs($financeManager, 'admin');
    expect(CurrencyResource::canAccess())->toBeTrue();

    // 3. Accountant is denied access to currency management
    $accountant = Admin::factory()->create();
    $roleAcc = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'admin']);
    $accountant->assignRole($roleAcc);
    $this->actingAs($accountant, 'admin');
    expect(CurrencyResource::canAccess())->toBeFalse();

    // 4. Base currency cannot be deleted
    $this->actingAs($superAdmin, 'admin');
    $baseCurrency = Currency::getBaseCurrency();
    expect(CurrencyResource::canDelete($baseCurrency))->toBeFalse();

    $eurCurrency = Currency::where('code', 'EUR')->first();
    expect(CurrencyResource::canDelete($eurCurrency))->toBeTrue();
});
