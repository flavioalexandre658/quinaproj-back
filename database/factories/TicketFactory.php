<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'collaborator_id' => \App\Models\Collaborator::factory(),
            'campaign_id' => \App\Models\Campaign::factory(),
            'number' => $this->faker->unique()->randomNumber(),
            'status' => $this->faker->randomElement(["0", "1", "-1"]),
        ];
    }
}
