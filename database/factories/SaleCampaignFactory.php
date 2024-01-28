<?php

namespace Database\Factories;

use App\Models\SaleCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleCampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleCampaign::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sale_id' => \App\Models\Sale::factory(),
            'campaign_id' => \App\Models\Campaign::factory(),
        ];
    }
}
