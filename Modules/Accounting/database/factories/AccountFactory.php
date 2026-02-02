<?php

namespace Modules\Accounting\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Accounting\Models\Account::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [

            'name' => 'test account',
            'number' => rand(1,5),
            
        ];
    }
}

