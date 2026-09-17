<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $clientRepository = app(ClientRepository::class);
    $this->client = $clientRepository->createClientCredentialsGrantClient('External Service Client');

    Passport::actingAsClient($this->client);

    $this->cashAccount = Account::factory()->create([
        'name' => 'Cash',
        'number' => 1010,
        'account_type_id' => AccountType::where('type', 'current_assets')->first()->id,
    ]);

    $this->revenueAccount = Account::factory()->create([
        'name' => 'Sales Revenue',
        'number' => 4010,
        'account_type_id' => AccountType::where('type', 'gross_sales')->first()->id,
    ]);

    $entry = JournalEntry::factory()->create([
        'date' => '2026-03-01',
        'type' => 'journal',
        'total_debit' => 1000,
        'total_credit' => 1000,
    ]);

    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id' => $this->cashAccount->id,
        'debit' => 1000,
        'credit' => 0,
        'source_type' => 'test',
        'source_reference' => 1,
    ]);

    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id' => $this->revenueAccount->id,
        'debit' => 0,
        'credit' => 1000,
        'source_type' => 'test',
        'source_reference' => 1,
    ]);
});

test('external trial balance report exports to PDF and Excel successfully', function () {
    $pdf = $this->getJson('/api/external/reports/trial-balance?export=pdf&endDate=2026-03-31');
    $pdf->assertStatus(200);
    expect($pdf->headers->get('Content-Type'))->toContain('application/pdf');
    expect($pdf->headers->get('Content-Disposition'))->toContain('attachment;');
    expect($pdf->headers->get('Content-Disposition'))->toContain('.pdf');

    $excel = $this->get('/api/external/reports/trial-balance?export=excel&endDate=2026-03-31');
    $excel->assertStatus(200);
    expect($excel->headers->get('Content-Disposition'))->toContain('attachment;');
    expect($excel->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('external general ledger report exports to PDF and Excel successfully', function () {
    $pdf = $this->getJson('/api/external/reports/general-ledger?accountNumber=1010&export=pdf');
    $pdf->assertStatus(200);
    expect($pdf->headers->get('Content-Type'))->toContain('application/pdf');

    $excel = $this->get('/api/external/reports/general-ledger?accountId=' . $this->cashAccount->id . '&export=excel');
    $excel->assertStatus(200);
    expect($excel->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('external income statement report exports to PDF and Excel successfully', function () {
    $pdf = $this->getJson('/api/external/reports/income-statement?export=pdf&startDate=2026-01-01&endDate=2026-03-31');
    $pdf->assertStatus(200);
    expect($pdf->headers->get('Content-Type'))->toContain('application/pdf');

    $excel = $this->get('/api/external/reports/income-statement?export=excel');
    $excel->assertStatus(200);
    expect($excel->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('external balance sheet report exports to PDF and Excel successfully', function () {
    $pdf = $this->getJson('/api/external/reports/balance-sheet?export=pdf&endDate=2026-03-31');
    $pdf->assertStatus(200);
    expect($pdf->headers->get('Content-Type'))->toContain('application/pdf');

    $excel = $this->get('/api/external/reports/balance-sheet?export=excel&endDate=2026-03-31');
    $excel->assertStatus(200);
    expect($excel->headers->get('Content-Disposition'))->toContain('.xlsx');
});
