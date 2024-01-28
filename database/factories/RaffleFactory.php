<?php

namespace Database\Factories;

use App\Models\Raffle;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaffleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Raffle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $factories = [
            [
                'name' => 'Loteria Federal',
                'url' => 'https://loterias.caixa.gov.br/Paginas/Federal.aspx'
            ],
            [
                'name' => 'Sorteador.com.br',
                'url' => 'https://sorteador.com.br'
            ],
            [
                'name' => 'Live no Instagram',
                'url' => 'https://instagram.com'
            ],
            [
                'name' => 'Live no Youtube',
                'url' => 'https://youtube.com'
            ],
            [
                'name' => 'Live no Tiktok',
                'url' => 'https://tiktok.com'
            ],
            [
                'name' => 'Outros',
                'url' => 'https://google.com'
            ]
        ];

        $randomFactory = $this->faker->unique()->randomElement($factories);

        return [
            'name' => $randomFactory['name'],
            'url' => $randomFactory['url'],
        ];
    }
}
