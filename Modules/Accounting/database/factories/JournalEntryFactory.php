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
        return [];
    }
}

