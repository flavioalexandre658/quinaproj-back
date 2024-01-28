<?php

namespace Database\Factories;

use App\Models\Collaborator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Collaborator::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $campaignId = \App\Models\Campaign::factory()->create()->id;
        $statusPayment = $this->faker->randomElement([0, 1]);

        $expireDate = Carbon::now('America/Sao_Paulo');
        $expireDate->addDays($this->faker->numberBetween(1, 30));

        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'amount_of_tickets' => $this->faker->numberBetween(1, 10),
            'price_each_ticket' => $this->faker->randomFloat(2, 0, 100),
            'status_payment' => $statusPayment,
            'campaign_id' => 121,
            'expire_date' => $expireDate,
        ];
    }
}
