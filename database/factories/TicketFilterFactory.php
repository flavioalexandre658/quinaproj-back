<?php

namespace Database\Factories;

use App\Models\TicketFilter;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFilterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketFilter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ticketFilters = [
            [
                'name' => 'Cliente escolhe os bilhetes manualmente',
                'description' => 'Cliente escolhe os bilhetes manualmente'
            ],
            [
                'name' => 'Sistema escolhe os bilhetes aleatoriamente',
                'description' => 'Sistema escolhe os bilhetes aleatoriamente'
            ]
        ];

        $randomFactory = $this->faker->unique()->randomElement($ticketFilters);

        return [
            'name' => $randomFactory['name'],
            'description' => $randomFactory['description'],
        ];
    }
}
