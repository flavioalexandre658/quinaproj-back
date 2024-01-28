<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'transaction_id' => $this->faker->uuid,
            'amount' => $this->faker->randomFloat(2, 10, 100),
            'currency' => $this->faker->currencyCode,
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
        ];
    }
}
