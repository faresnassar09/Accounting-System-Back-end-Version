<?php

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\FixedAsset;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\CoreAccounting\FixedAssetService;
use Modules\Admin\Filament\Resources\FixedAssets\FixedAssetResource;
use Modules\Admin\Models\Admin;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->nonCurrentAssetType = AccountType::firstOrCreate(['type' => 'non_current_assets', 'account_group' => 'assets']);
    $this->currentAssetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->expenseType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);

    $this->assetCostAcc = Account::factory()->create([
        'name'            => 'Machinery Cost',
        'number'          => 1510,
        'account_type_id' => $this->nonCurrentAssetType->id,
    ]);

    $this->accumDeprAcc = Account::factory()->create([
        'name'            => 'Accumulated Depreciation - Machinery',
        'number'          => 1519,
        'account_type_id' => $this->nonCurrentAssetType->id,
    ]);

    $this->deprExpAcc = Account::factory()->create([
        'name'            => 'Machinery Depreciation Expense',
        'number'          => 6020,
        'account_type_id' => $this->expenseType->id,
    ]);

    $this->cashAcc = Account::factory()->create([
        'name'            => 'Bank Operating',
        'number'          => 1010,
        'account_type_id' => $this->currentAssetType->id,
    ]);

    $this->gainLossAcc = Account::factory()->create([
        'name'            => 'Gain/Loss on Asset Disposal',
        'number'          => 8010,
        'account_type_id' => $this->revType->id,
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('straight-line monthly depreciation posts balanced journal entry and updates book value', function () {
    $service = app(FixedAssetService::class);

    // Asset cost: 12,000, salvage: 0, useful life: 12 months => 1,000 / month
    $asset = FixedAsset::create([
        'asset_number'                       => 'FA-MCH-001',
        'name'                               => 'CNC Milling Machine',
        'category'                           => 'equipment',
        'purchase_date'                      => '2026-01-01',
        'purchase_cost'                      => 12000.00,
        'salvage_value'                      => 0.00,
        'useful_life_months'                 => 12,
        'depreciation_method'                => 'straight_line',
        'asset_account_id'                   => $this->assetCostAcc->id,
        'depreciation_expense_account_id'    => $this->deprExpAcc->id,
        'accumulated_depreciation_account_id'=> $this->accumDeprAcc->id,
        'accumulated_depreciation'           => 0.00,
        'book_value'                         => 12000.00,
        'status'                             => 'active',
    ]);

    $period = Carbon::parse('2026-01-01');
    $entry = $service->postMonthlyDepreciation($asset, $period);

    expect($entry)->not->toBeNull();
    expect((float) $entry->total_debit)->toBe(1000.00);
    expect((float) $entry->total_credit)->toBe(1000.00);
    expect($entry->lines)->toHaveCount(2);

    $asset->refresh();
    expect((float) $asset->accumulated_depreciation)->toBe(1000.00);
    expect((float) $asset->book_value)->toBe(11000.00);
    expect($asset->status)->toBe('active');
    expect($asset->schedules)->toHaveCount(1);
});

test('posting duplicate depreciation for the same period throws DomainException', function () {
    $service = app(FixedAssetService::class);

    $asset = FixedAsset::create([
        'asset_number'                       => 'FA-MCH-002',
        'name'                               => 'Lathe Machine',
        'category'                           => 'equipment',
        'purchase_date'                      => '2026-01-01',
        'purchase_cost'                      => 6000.00,
        'salvage_value'                      => 0.00,
        'useful_life_months'                 => 6,
        'depreciation_method'                => 'straight_line',
        'asset_account_id'                   => $this->assetCostAcc->id,
        'depreciation_expense_account_id'    => $this->deprExpAcc->id,
        'accumulated_depreciation_account_id'=> $this->accumDeprAcc->id,
        'accumulated_depreciation'           => 0.00,
        'book_value'                         => 6000.00,
        'status'                             => 'active',
    ]);

    $period = Carbon::parse('2026-01-01');
    $service->postMonthlyDepreciation($asset, $period);

    $this->expectException(\DomainException::class);
    $service->postMonthlyDepreciation($asset, $period);
});

test('disposing fixed asset correctly recognizes proceeds, derecognizes cost & accumulated depr, and balances gain/loss', function () {
    $service = app(FixedAssetService::class);

    // Asset cost: 10,000, already depreciated: 4,000 => Net Book Value = 6,000
    // Sold for 7,500 => Gain on disposal = 1,500
    $asset = FixedAsset::create([
        'asset_number'                       => 'FA-VEH-001',
        'name'                               => 'Delivery Truck',
        'category'                           => 'vehicles',
        'purchase_date'                      => '2025-01-01',
        'purchase_cost'                      => 10000.00,
        'salvage_value'                      => 1000.00,
        'useful_life_months'                 => 36,
        'depreciation_method'                => 'straight_line',
        'asset_account_id'                   => $this->assetCostAcc->id,
        'depreciation_expense_account_id'    => $this->deprExpAcc->id,
        'accumulated_depreciation_account_id'=> $this->accumDeprAcc->id,
        'accumulated_depreciation'           => 4000.00,
        'book_value'                         => 6000.00,
        'status'                             => 'active',
    ]);

    $disposalDate = Carbon::parse('2026-06-15');
    $entry = $service->disposeAsset(
        $asset,
        7500.00,
        $disposalDate,
        $this->cashAcc->id,
        $this->gainLossAcc->id
    );

    expect($entry)->not->toBeNull();
    // Debits: Cash 7500 + Accum 4000 = 11,500. Credits: Asset 10,000 + Gain 1,500 = 11,500.
    expect((float) $entry->total_debit)->toBe(11500.00);
    expect((float) $entry->total_credit)->toBe(11500.00);

    $asset->refresh();
    expect($asset->status)->toBe('disposed');
    expect((float) $asset->disposal_amount)->toBe(7500.00);
    expect((float) $asset->book_value)->toBe(0.00);
});

test('fixed asset resource can be accessed by authorized admins', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin, 'admin');

    expect(FixedAssetResource::canAccess())->toBeTrue();
});
