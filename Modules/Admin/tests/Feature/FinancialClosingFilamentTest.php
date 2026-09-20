<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\ClosedFinancialYear;
use Modules\Accounting\Models\JournalEntry;
use Modules\Admin\Filament\Pages\FinancialClosing\FinancialClosingPage;
use Modules\Admin\Models\Admin;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'closing-tenant.localhost']);
    tenancy()->initialize($this->tenant);

    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $revenueType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $expenseType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $equityType = AccountType::firstOrCreate(['type' => 'retained_earnings', 'account_group' => 'equity']);

    $this->revenueAcc = Account::factory()->create(['account_type_id' => $revenueType->id, 'number' => 4001, 'name' => 'Sales']);
    $this->expenseAcc = Account::factory()->create(['account_type_id' => $expenseType->id, 'number' => 5001, 'name' => 'Rent']);
    $this->retainedEarningsAcc = Account::factory()->create(['account_type_id' => $equityType->id, 'number' => 3001, 'name' => 'Retained Earnings']);
    $this->cashAcc = Account::factory()->create(['number' => 1001, 'name' => 'Cash']);
});

test('admin can access and render Financial Closing page in Filament', function () {
    Livewire::test(FinancialClosingPage::class)
        ->assertSuccessful()
        ->assertSee('FINANCIAL YEAR-END CLOSING & ROLL-FORWARD')
        ->assertSee('Period Status');
});

test('admin can preview P&L and execute financial year closing in Filament', function () {
    // 1. Post transactions in 2025
    $entry = JournalEntry::create([
        'reference'   => 'REV-2025-01',
        'date'        => '2025-06-15',
        'description' => '2025 Revenue Entry',
        'total_debit' => 10000,
        'total_credit'=> 10000,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    $entry->lines()->create([
        'source_type' => 'admin', 'source_reference' => (string) $this->admin->id,
        'account_id' => $this->cashAcc->id, 'debit' => 10000, 'credit' => 0, 'date' => '2025-06-15',
    ]);
    $entry->lines()->create([
        'source_type' => 'admin', 'source_reference' => (string) $this->admin->id,
        'account_id' => $this->revenueAcc->id, 'debit' => 0, 'credit' => 10000, 'date' => '2025-06-15',
    ]);

    // 2. Load page for year 2025
    $component = Livewire::test(FinancialClosingPage::class)
        ->set('selectedYear', 2025)
        ->set('retainedEarningsAccountId', $this->retainedEarningsAcc->id);

    expect($component->get('isClosed'))->toBeFalse();
    expect($component->get('pnlPreview.total_revenues'))->toBe(10000.0);
    expect($component->get('pnlPreview.net_profit'))->toBe(10000.0);

    // 3. Apply Closing
    $component->call('applyClosing');

    // 4. Assert year is closed in DB
    $this->assertDatabaseHas('closed_financial_years', [
        'year'                         => 2025,
        'retained_earnings_account_id' => $this->retainedEarningsAcc->id,
    ]);

    // 5. Assert closing journal entry was generated
    $this->assertDatabaseHas('journal_entries', [
        'type'   => 'closing',
        'status' => 'approved',
    ]);

    // 6. Assert next year opening entry was generated
    $this->assertDatabaseHas('journal_entries', [
        'type'   => 'opening',
        'status' => 'approved',
    ]);

    // Component state now reflects isClosed == true
    $component->refresh();
    expect($component->get('isClosed'))->toBeTrue();
});

test('admin cannot re-close an already closed financial year', function () {
    ClosedFinancialYear::create([
        'year'                         => 2024,
        'net_profit_loss'              => 5000,
        'retained_earnings_account_id' => $this->retainedEarningsAcc->id,
        'closed_by'                    => null,
    ]);

    Livewire::test(FinancialClosingPage::class)
        ->set('selectedYear', 2024)
        ->set('retainedEarningsAccountId', $this->retainedEarningsAcc->id)
        ->call('applyClosing');

    // Should only have the one record created in setup
    expect(ClosedFinancialYear::where('year', 2024)->count())->toBe(1);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
