<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Admin\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use Modules\Admin\Filament\Resources\JournalEntries\Pages\ViewJournalEntry;
use Modules\Admin\Models\Admin;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->account = Account::factory()->create();
});

test('admin can view journal entries table in filament', function () {
    $entry = JournalEntry::create([
        'reference'   => 'JE-FIL-001',
        'date'        => now(),
        'description' => 'Test Filament Entry',
        'total_debit' => 1000,
        'total_credit'=> 1000,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);

    Livewire::test(ListJournalEntries::class)
        ->assertCanSeeTableRecords([$entry]);
});

test('admin can reverse a journal entry via filament action', function () {
    $entry = JournalEntry::create([
        'reference'   => 'JE-FIL-REV',
        'date'        => '2026-03-10',
        'description' => 'Entry to reverse in Filament',
        'total_debit' => 1500,
        'total_credit'=> 1500,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);

    $entry->lines()->create([
        'source_type'      => 'user',
        'source_reference' => (string) $this->admin->id,
        'account_id'       => $this->account->id,
        'debit'            => 1500,
        'credit'           => 0,
        'date'             => '2026-03-10',
    ]);
    $entry->lines()->create([
        'source_type'      => 'user',
        'source_reference' => (string) $this->admin->id,
        'account_id'       => $this->account->id,
        'debit'            => 0,
        'credit'           => 1500,
        'date'             => '2026-03-10',
    ]);

    Livewire::test(ListJournalEntries::class)
        ->callTableAction('reverse', $entry, [
            'reason'        => 'Filament user mistake',
            'reversal_date' => '2026-03-11',
        ])
        ->assertHasNoErrors();

    $entry->refresh();
    expect($entry->status)->toBe('cancled');

    $this->assertDatabaseHas('journal_entries', [
        'reference' => 'REV-JE-FIL-REV',
        'type'      => 'adjustment',
        'status'    => 'approved',
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
