<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;




uses(TestCase::class, DatabaseMigrations::class);
beforeEach(function () {

    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant' , 'guard_name' => 'web']);
    $this->user->assignRole($role);

    Passport::actingAs($this->user);


    $this->account = Account::factory()->create();

});   


test('user can create a entry journal ',function(){


    $data = [

        'journalHeader' => [
            'reference' => rand(1,5),
            'date' => now(),
            'description' => 'test entry',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ],

        'lines' => [
            
            [

            'account_id' => $this->account->id,
            'debit' => 1000,
            'credit'=> 0,
        ],
        [

            
            'account_id' => $this->account->id,
            'debit' => 0,
            'credit'=> 1000,
        ]]
    ];

    $response = $this->postJson('api/v1/accounting/journal-entries',$data,[

        'Accept' => 'application/json'
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('journal_entries',
     ['reference' => $data['journalHeader']['reference']]);

     $this->assertDatabaseHas('journal_entry_lines', [
        'account_id' => $this->account->id,
        'source_reference' => (string) $this->user->id,
    ]);

});


test("can't create unbalanced journal entry",function(){

    $data = [

            'journalHeader' => [
                'reference' => 'UNBALANCED-REF',
                'date' => now(),
                'description' => 'test entry',
                'total_debit' => 1000,
                'total_credit' => 900,
            ],

            'lines' => [

                [

                    'account_id' => $this->account->id,
                    'debit' => 1000,
                    'credit'=> 0,
                ],
                [
        
                    
                    'account_id' => $this->account->id,
                    'debit' => 0,
                    'credit'=> 900,
                ]
                ] 

    ];



    $response = $this->postJson('api/v1/accounting/journal-entries',$data,
[
    'Accept' => 'application/json'
]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('journal_entries',
     ['reference' => $data['journalHeader']['reference']]);

     $this->assertDatabaseMissing('journal_entry_lines', [
        'account_id' => $this->account->id
    ]);

});

test("can't create journal entry with a duplicate reference", function () {

 $commonReference = 'REF-100';

    $data1 = [
        'journalHeader' => [
            'reference' => $commonReference,
            'date' => now()->toDateString(),
            'description' => 'First Entry',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ],
        'lines' => [
            ['account_id' => $this->account->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $this->account->id, 'debit' => 0, 'credit' => 1000]
        ]
    ];

    $data2 = $data1;
    $data2['journalHeader']['description'] = 'Duplicate Entry Attempt';

    $this->postJson('api/v1/accounting/journal-entries', $data1, [
        'Accept' => 'application/json',
        'Idempotency-Key' => 'key-request-1' 
    ]);

    $response = $this->postJson('api/v1/accounting/journal-entries', $data2, [
        'Accept' => 'application/json',
        'Idempotency-Key' => 'key-request-2' 
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['journalHeader.reference']);
});

test('user can list journal entries with pagination and filters', function () {
    $entry1 = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-LIST-001',
        'date' => '2026-01-15',
        'description' => 'First list entry',
        'total_debit' => 500,
        'total_credit' => 500,
        'type' => 'journal',
        'status' => 'approved',
    ]);
    $entry2 = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-LIST-002',
        'date' => '2026-02-15',
        'description' => 'Second list entry',
        'total_debit' => 800,
        'total_credit' => 800,
        'type' => 'opening',
        'status' => 'approved',
    ]);

    $response = $this->getJson('api/v1/accounting/journal-entries');
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    expect($response->json('data.data'))->toBeArray();

    // Filter by type
    $filteredResponse = $this->getJson('api/v1/accounting/journal-entries?type=opening');
    $filteredResponse->assertStatus(200);
    $items = $filteredResponse->json('data.data');
    foreach ($items as $item) {
        expect($item['type'])->toBe('opening');
    }
});

test('user can view a single journal entry with its lines', function () {
    $entry = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-SHOW-001',
        'date' => now(),
        'description' => 'Single view entry',
        'total_debit' => 1500,
        'total_credit' => 1500,
        'type' => 'journal',
        'status' => 'approved',
    ]);

    $entry->lines()->create([
        'source_type' => 'user',
        'source_reference' => (string) $this->user->id,
        'account_id' => $this->account->id,
        'debit' => 1500,
        'credit' => 0,
        'date' => now(),
    ]);
    $entry->lines()->create([
        'source_type' => 'user',
        'source_reference' => (string) $this->user->id,
        'account_id' => $this->account->id,
        'debit' => 0,
        'credit' => 1500,
        'date' => now(),
    ]);

    $response = $this->getJson("api/v1/accounting/journal-entries/{$entry->id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.reference', 'JE-SHOW-001');
    expect($response->json('data.lines'))->toHaveCount(2);
});

test('user can reverse a posted journal entry, creating balancing entry and marking original cancelled', function () {
    $entry = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-REV-001',
        'date' => '2026-03-01',
        'description' => 'Entry to reverse',
        'total_debit' => 2000,
        'total_credit' => 2000,
        'type' => 'journal',
        'status' => 'approved',
    ]);

    $entry->lines()->create([
        'source_type' => 'user',
        'source_reference' => (string) $this->user->id,
        'account_id' => $this->account->id,
        'debit' => 2000,
        'credit' => 0,
        'date' => '2026-03-01',
    ]);
    $entry->lines()->create([
        'source_type' => 'user',
        'source_reference' => (string) $this->user->id,
        'account_id' => $this->account->id,
        'debit' => 0,
        'credit' => 2000,
        'date' => '2026-03-01',
    ]);

    $response = $this->postJson("api/v1/accounting/journal-entries/{$entry->id}/reverse", [
        'reason' => 'Incorrect amount entered',
        'reversal_date' => '2026-03-02',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);

    // Original entry is marked cancelled
    $entry->refresh();
    expect($entry->status)->toBe('cancled');

    // New reversing entry was created
    $this->assertDatabaseHas('journal_entries', [
        'reference' => 'REV-JE-REV-001',
        'type' => 'adjustment',
        'status' => 'approved',
        'total_debit' => 2000,
        'total_credit' => 2000,
    ]);

    $reversingEntry = \Modules\Accounting\Models\JournalEntry::where('reference', 'REV-JE-REV-001')->first();
    expect($reversingEntry->lines)->toHaveCount(2);

    $line1 = $reversingEntry->lines->first();
    expect((float) $line1->debit)->toEqual(0.0);
    expect((float) $line1->credit)->toEqual(2000.0);
});

test('user cannot reverse an already cancelled entry', function () {
    $entry = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-ALREADY-CANCLED',
        'date' => '2026-03-01',
        'description' => 'Already cancelled',
        'total_debit' => 100,
        'total_credit' => 100,
        'type' => 'journal',
        'status' => 'cancled',
    ]);

    $response = $this->postJson("api/v1/accounting/journal-entries/{$entry->id}/reverse", [
        'reason' => 'Duplicate reversal attempt',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
});

test('user cannot reverse an entry in a closed financial year', function () {
    \Modules\Accounting\Models\ClosedFinancialYear::create([
        'closed_by' => $this->user->id,
        'retained_earnings_account_id' => $this->account->id,
        'year' => '2024',
        'net_profit_loss' => 0.00,
    ]);

    $entry = \Modules\Accounting\Models\JournalEntry::create([
        'reference' => 'JE-CLOSED-YEAR',
        'date' => '2024-06-15',
        'description' => 'Entry in closed year',
        'total_debit' => 500,
        'total_credit' => 500,
        'type' => 'journal',
        'status' => 'approved',
    ]);

    $response = $this->postJson("api/v1/accounting/journal-entries/{$entry->id}/reverse", [
        'reason' => 'Cannot reverse closed year',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    expect($response->json('message'))->toContain('closed financial year');
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end(); 
        $tenant->delete();
    }
});