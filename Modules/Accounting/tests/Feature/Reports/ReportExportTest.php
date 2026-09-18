<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Modules\Accounting\Jobs\GenerateAndSendReportJob;
use Modules\Accounting\Mail\FinancialReportMail;
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

test('trial balance export queues report generation and returns non-blocking JSON response', function () {
    Queue::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?export=pdf&endDate=2026-03-31');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('message'))->toContain('Trial Balance report generation has been queued');

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'trial-balance'
            && $job->parameters['endDate'] === '2026-03-31'
            && $job->formats === ['pdf']
            && $job->recipientEmail === $this->user->email;
    });
});

test('trial balance queues email with explicit recipient email and multiple attachments', function () {
    Queue::fake();

    $response = $this->getJson('/api/v1/accounting/reports/trial-balance?send_email=1&attachments=pdf,excel&email=fares.ahmed.nassar0@gmail.com&endDate=2026-12-31');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'trial-balance'
            && $job->recipientEmail === 'fares.ahmed.nassar0@gmail.com'
            && count($job->formats) === 2
            && in_array('pdf', $job->formats)
            && in_array('excel', $job->formats);
    });
});

test('trial balance queued job generates and sends email with attachments', function () {
    Mail::fake();

    $job = new GenerateAndSendReportJob(
        reportType: 'trial-balance',
        parameters: ['endDate' => '2026-03-31'],
        recipientEmail: 'fares.ahmed.nassar0@gmail.com',
        formats: ['pdf', 'excel'],
        tenantId: $this->tenant->id
    );

    app()->call([$job, 'handle']);

    Mail::assertSent(FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'trial_balance_2026-03-31.pdf'
            && $attachments[1]->as === 'trial_balance_2026-03-31.xlsx';
    });
});

test('general ledger export queues report generation and returns non-blocking JSON response', function () {
    Queue::fake();

    $response = $this->getJson('/api/v1/accounting/reports/general-ledger?accountId=' . $this->cashAccount->id . '&export=excel');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('message'))->toContain('General Ledger report generation has been queued');

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'general-ledger'
            && (int) $job->parameters['accountId'] === (int) $this->cashAccount->id
            && $job->formats === ['excel'];
    });
});

test('general ledger queued job generates and sends email with attachments', function () {
    Mail::fake();

    $job = new GenerateAndSendReportJob(
        reportType: 'general-ledger',
        parameters: ['accountId' => $this->cashAccount->id, 'endDate' => '2026-03-31'],
        recipientEmail: 'fares.ahmed.nassar0@gmail.com',
        formats: ['pdf', 'excel'],
        tenantId: $this->tenant->id
    );

    app()->call([$job, 'handle']);

    Mail::assertSent(FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && str_contains($attachments[0]->as, 'general_ledger_')
            && str_ends_with($attachments[0]->as, '.pdf')
            && str_contains($attachments[1]->as, 'general_ledger_')
            && str_ends_with($attachments[1]->as, '.xlsx');
    });
});

test('income statement export queues report generation and returns non-blocking JSON response', function () {
    Queue::fake();

    $response = $this->getJson('/api/v1/accounting/reports/income-statement?export=pdf&startDate=2026-01-01&endDate=2026-03-31');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('message'))->toContain('Income Statement report generation has been queued');

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'income-statement'
            && $job->parameters['startDate'] === '2026-01-01'
            && $job->parameters['endDate'] === '2026-03-31'
            && $job->formats === ['pdf'];
    });
});

test('income statement queued job generates and sends email with attachments', function () {
    Mail::fake();

    $job = new GenerateAndSendReportJob(
        reportType: 'income-statement',
        parameters: ['startDate' => '2026-01-01', 'endDate' => '2026-03-31'],
        recipientEmail: 'fares.ahmed.nassar0@gmail.com',
        formats: ['pdf', 'excel'],
        tenantId: $this->tenant->id
    );

    app()->call([$job, 'handle']);

    Mail::assertSent(FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'income_statement_2026-03-31.pdf'
            && $attachments[1]->as === 'income_statement_2026-03-31.xlsx';
    });
});

test('balance sheet export queues report generation and returns non-blocking JSON response', function () {
    Queue::fake();

    $response = $this->getJson('/api/v1/accounting/reports/balance-sheet?export=excel&endDate=2026-03-31');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('message'))->toContain('Balance Sheet report generation has been queued');

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'balance-sheet'
            && $job->parameters['endDate'] === '2026-03-31'
            && $job->formats === ['excel'];
    });
});

test('balance sheet queued job generates and sends email with attachments', function () {
    Mail::fake();

    $job = new GenerateAndSendReportJob(
        reportType: 'balance-sheet',
        parameters: ['endDate' => '2026-03-31'],
        recipientEmail: 'fares.ahmed.nassar0@gmail.com',
        formats: ['pdf', 'excel'],
        tenantId: $this->tenant->id
    );

    app()->call([$job, 'handle']);

    Mail::assertSent(FinancialReportMail::class, function ($mail) {
        $attachments = $mail->attachments();
        return $mail->hasTo('fares.ahmed.nassar0@gmail.com')
            && count($attachments) === 2
            && $attachments[0]->as === 'balance_sheet_2026-03-31.pdf'
            && $attachments[1]->as === 'balance_sheet_2026-03-31.xlsx';
    });
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
