<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories = [
            'Ação entre amigos',
            'Beneficente',
            'Eletrônicos',
            'Carros',
            'Motos',
            'Esportes',
            'Beleza',
            'Infantil',
            'Serviços',
            'Imóveis',
            'Agro',
            'Outros'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($categories)
        ];
    }
}
