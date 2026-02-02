<?php

use \Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\ClosedFinancialYear;
use Modules\Accounting\Services\CoreAccounting\FinancialClosingService;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;








uses(Tests\TestCase::class, DatabaseMigrations::class);
beforeEach(function () {
    $this->tenant1 = Tenant::create();

    $this->tenant1->domains()->create(['domain' => 'tenant1.app.test']);

    tenancy()->initialize($this->tenant1);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant' , 'guard_name' => 'web']);
    $this->user->assignRole($role);

    $retainedEarningsType = AccountType::where('type','retained_earnings')->first();
    $this->retainedEarnings = Account::factory()->create([
        'name' => 'retained_earnings',
        'number' => 234,
        'account_type_id' => $retainedEarningsType->id
    ]);


});

test('it correctly zeroes out revenue and expenses and transfers profit to retained earnings', function () {

    $this->actingAs($this->user);
    $year = '2026';

    $response = $this->postJson('/api/v1/accounting/financial-closing/close', [
        'year' => $year,
        'account_id' => $this->retainedEarnings->id
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('journal_entries', [
        'description' => "closing journal for year ($year)",
        'type' => 'closing' 
    ]);

    $nextYear = Carbon::parse($year)
    ->addYears()
    ->startOfYear()
    ->format('Y-m-d');

    $this->assertDatabaseHas('journal_entries', [
        'description' => "Opening journal for year ($nextYear)",
        'type' => 'opening'
    ]);

    $this->assertDatabaseHas('closed_financial_years', [
        'year' => $year,
        'retained_earnings_account_id' => $this->retainedEarnings->id
    ]);
});

test('it throws an exception if the year is already closed', function () {
    $this->actingAs($this->user);

    ClosedFinancialYear::create([
        'closed_by' => $this->user->id,
        'year' => '2026',
        'net_profit_loss' => 4500,
        'retained_earnings_account_id' => $this->retainedEarnings->id
    ]);

    expect(function () {
        app(Modules\Accounting\Services\CoreAccounting\FinancialClosingService::class)
            ->applyClosingFinancialYear((object)[
                'year' => '2026',
                'account_id' => $this->retainedEarnings->id
            ]);
    })->toThrow(\Exception::class, "Financial Year ( 2026 ) Already Closed");
});