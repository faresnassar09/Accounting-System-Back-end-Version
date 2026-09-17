<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Authorization\Models\Role;
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

    $this->cashAccount = Account::factory()->create([
        'name' => 'Cash Account',
        'number' => 1010,
        'account_type_id' => AccountType::where('type', 'current_assets')->first()->id,
    ]);

    $this->revenueAccount = Account::factory()->create([
        'name' => 'Sales Revenue',
        'number' => 4010,
        'account_type_id' => AccountType::where('type', 'gross_sales')->first()->id,
    ]);

    $this->expenseAccount = Account::factory()->create([
        'name' => 'Rent Expense',
        'number' => 6010,
        'account_type_id' => AccountType::where('type', 'operating_expenses')->first()->id,
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

test('trial balance exports to PDF successfully', function () {
    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?export=pdf&endDate=2026-03-31');

    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment;');
    expect($response->headers->get('Content-Disposition'))->toContain('.pdf');
});

test('trial balance export sends email containing both PDF and Excel reports when explicitly requested', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?export=pdf&send_email=1&attachments=pdf,excel&email=fares.ahmed.nassar0@gmail.com&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'trial_balance_2026-12-31.pdf'
            && $attachments[1]->as === 'trial_balance_2026-12-31.xlsx';
    });
});

test('trial balance sends email to authenticated user when email parameter is omitted but send_email is true', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?send_email=1&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});

test('trial balance sends email with only PDF attachment when requested', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?send_email=1&attachments=pdf&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return count($attachments) === 1 && $attachments[0]->as === 'trial_balance_2026-12-31.pdf';
    });
});

test('trial balance sends email with only Excel attachment when requested', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?send_email=1&attachments=excel&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return count($attachments) === 1 && $attachments[0]->as === 'trial_balance_2026-12-31.xlsx';
    });
});

test('trial balance download does not send unwanted emails when send_email is omitted', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?export=pdf&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});

test('trial balance exports to Excel successfully', function () {
    $response = $this->get('/api/v1/accounting/reports/trial-balance?export=excel&endDate=2026-03-31');

    $response->assertStatus(200);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment;');
    expect($response->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('general ledger exports to PDF and Excel successfully', function () {
    $pdfResponse = $this->getJson('/api/v1/accounting/reports/general-ledger?accountId=' . $this->cashAccount->id . '&export=pdf');
    $pdfResponse->assertStatus(200);
    expect($pdfResponse->headers->get('Content-Type'))->toContain('application/pdf');
    expect($pdfResponse->headers->get('Content-Disposition'))->toContain('.pdf');

    $excelResponse = $this->get('/api/v1/accounting/reports/general-ledger?accountId=' . $this->cashAccount->id . '&export=excel');
    $excelResponse->assertStatus(200);
    expect($excelResponse->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('income statement exports to PDF and Excel successfully', function () {
    $pdfResponse = $this->getJson('/api/v1/accounting/reports/income-statement?export=pdf&startDate=2026-01-01&endDate=2026-03-31');
    $pdfResponse->assertStatus(200);
    expect($pdfResponse->headers->get('Content-Type'))->toContain('application/pdf');
    expect($pdfResponse->headers->get('Content-Disposition'))->toContain('.pdf');

    $excelResponse = $this->get('/api/v1/accounting/reports/income-statement?export=excel');
    $excelResponse->assertStatus(200);
    expect($excelResponse->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('balance sheet exports to PDF and Excel successfully', function () {
    $pdfResponse = $this->getJson('/api/v1/accounting/reports/balance-sheet?export=pdf&endDate=2026-03-31');
    $pdfResponse->assertStatus(200);
    expect($pdfResponse->headers->get('Content-Type'))->toContain('application/pdf');
    expect($pdfResponse->headers->get('Content-Disposition'))->toContain('.pdf');

    $excelResponse = $this->get('/api/v1/accounting/reports/balance-sheet?export=excel&endDate=2026-03-31');
    $excelResponse->assertStatus(200);
    expect($excelResponse->headers->get('Content-Disposition'))->toContain('.xlsx');
});

test('standard JSON response is preserved when export parameter is omitted', function () {
    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?endDate=2026-03-31');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'end_date',
            'reportData',
            'totals' => ['total_debit', 'total_credit', 'isBalanced'],
        ],
    ]);
});

test('general ledger sends email with requested attachments', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/general-ledger?accountId=' . $this->cashAccount->id . '&send_email=1&attachments=pdf,excel&email=fares.ahmed.nassar0@gmail.com');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && str_contains($attachments[0]->as, 'general_ledger_')
            && str_ends_with($attachments[0]->as, '.pdf')
            && str_contains($attachments[1]->as, 'general_ledger_')
            && str_ends_with($attachments[1]->as, '.xlsx');
    });
});

test('general ledger download does not send unwanted emails when send_email is omitted', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/general-ledger?accountId=' . $this->cashAccount->id . '&export=pdf');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});

test('income statement sends email with requested attachments', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/income-statement?send_email=1&attachments=pdf,excel&email=fares.ahmed.nassar0@gmail.com&startDate=2026-01-01&endDate=2026-03-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'income_statement_2026-03-31.pdf'
            && $attachments[1]->as === 'income_statement_2026-03-31.xlsx';
    });
});

test('income statement download does not send unwanted emails when send_email is omitted', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/income-statement?export=pdf&startDate=2026-01-01&endDate=2026-03-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});

test('balance sheet sends email with requested attachments', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/balance-sheet?send_email=1&attachments=pdf,excel&email=fares.ahmed.nassar0@gmail.com&endDate=2026-03-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertSent(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'balance_sheet_2026-03-31.pdf'
            && $attachments[1]->as === 'balance_sheet_2026-03-31.xlsx';
    });
});

test('balance sheet download does not send unwanted emails when send_email is omitted', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/balance-sheet?export=pdf&endDate=2026-03-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});

test('queued email dispatch pushes FinancialReportMail to queue', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?send_email=1&queue=1&endDate=2026-12-31');
    $response->assertStatus(200);

    \Illuminate\Support\Facades\Mail::assertQueued(\Modules\Accounting\Mail\FinancialReportMail::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});
