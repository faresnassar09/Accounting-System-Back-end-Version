<?php

namespace Modules\Accounting\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Accounting\Models\JournalEntry::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [

            'user_id' => auth()->id(),
            'reference' => rand(1,5),
            'date' => now(),
            'description' => 'test entry',
            'total_debit' => 0,
            'total_credit' => 0
            
                ];
    }
}

