<?php

namespace Modules\Accounting\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryLineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Accounting\Models\JournalEntryLine::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [

            'account_id' => null,
            'debit' => 0,
            'credit' => 0,
            'journal_entry_id' => null,
        
        ];
    }
}

