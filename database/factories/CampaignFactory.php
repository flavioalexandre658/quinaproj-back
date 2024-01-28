<?php

namespace Database\Factories;

use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categoryId = \App\Models\Category::factory()->create()->id;
        $ticketFilterId = \App\Models\TicketFilter::factory()->create()->id;
        $raffleId = \App\Models\Raffle::factory()->create()->id;
        $feeId = \App\Models\Fee::factory()->create()->id;
        $userId = \App\Models\User::factory()->create()->id;

        $status = $this->faker->randomElement([1, 0]);
        $statusPayment = $this->faker->randomElement([0, 1]);
        $showDateOfRaffle = $this->faker->boolean;

        $dateOfRaffle = null;
        if ($showDateOfRaffle) {
            $dateOfRaffle = Carbon::now('America/Sao_Paulo');
            $dateOfRaffle->addDays($this->faker->numberBetween(1, 30));
        }

        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'image' => $this->faker->imageUrl(),
            'amount_tickets' => $this->faker->numberBetween(1, 100),
            'available_tickets' => $this->faker->numberBetween(0, 100),
            'pending_tickets' => $this->faker->numberBetween(0, 100),
            'unavailable_tickets' => $this->faker->numberBetween(0, 100),
            'support_number' => $this->faker->phoneNumber,
            'status' => $status,
            'status_payment' => $statusPayment,
            'price_each_ticket' => $this->faker->randomFloat(2, 1, 100),
            'min_ticket' => $this->faker->numberBetween(1, 10),
            'max_ticket' => $this->faker->numberBetween(11, 100),
            'show_date_of_raffle' => $showDateOfRaffle,
            'date_of_raffle' => $dateOfRaffle,
            'time_wait_payment' => $this->faker->numberBetween(1, 7),
            'allow_terms' => $this->faker->boolean,
            'category_id' => $categoryId,
            'ticket_filter_id' => $ticketFilterId,
            'raffle_id' => $raffleId,
            'fee_id' => $feeId,
            'user_id' => $userId,
        ];
    }
}
