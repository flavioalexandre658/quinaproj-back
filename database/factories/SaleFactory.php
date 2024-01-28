<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userId = \App\Models\User::factory()->create()->id;

        return [
            'amount_tickets' => $this->faker->randomNumber(),
            'price_amount' => $this->faker->randomFloat(2, 0, 100),
            'user_id' => 246
        ];
    }
}
