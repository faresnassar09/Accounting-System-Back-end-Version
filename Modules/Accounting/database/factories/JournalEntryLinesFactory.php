<?php

namespace Modules\Accounting\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryLinesFactory extends Factory
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
        return [];
    }
}

