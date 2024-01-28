<?php

namespace Database\Factories;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Fee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $index = 0;

        $fees = [
            [
                'revenue' => 'R$ 0,00 a R$ 100,00',
                'min_revenue' => 0.00,
                'max_revenue' => 100.00,
                'fee' => 'R$ 7,00'
            ],
            [
                'revenue' => 'R$ 101,00 a R$ 200,00',
                'min_revenue' => 101.00,
                'max_revenue' => 200.00,
                'fee' => 'R$ 17,00'
            ],
            [
                'revenue' => 'R$ 201,00 a R$ 400,00',
                'min_revenue' => 201.00,
                'max_revenue' => 400.00,
                'fee' => 'R$ 27,00'
            ],
            [
                'revenue' => 'R$ 401,00 a R$ 701,00',
                'min_revenue' => 401.00,
                'max_revenue' => 701.00,
                'fee' => 'R$ 37,00'
            ],
            [
                'revenue' => 'R$ 701,00 a R$ 1.000,00',
                'min_revenue' => 701.00,
                'max_revenue' => 1000.00,
                'fee' => 'R$ 47,00'
            ],
            [
                'revenue' => 'R$ 1.001,00 a R$ 2.000,00',
                'min_revenue' => 1001.00,
                'max_revenue' => 2000.00,
                'fee' => 'R$ 67,00'
            ],
            [
                'revenue' => 'R$ 2.001,00 a R$ 4.000,00',
                'min_revenue' => 2001.00,
                'max_revenue' => 4000.00,
                'fee' => 'R$ 77,00'
            ],
            [
                'revenue' => 'R$ 4.001,00 a R$ 7.100,00',
                'min_revenue' => 4001.00,
                'max_revenue' => 7100.00,
                'fee' => 'R$ 127,00'
            ],
            [
                'revenue' => 'R$ 7.101,00 a R$ 10.000,00',
                'min_revenue' => 7101.00,
                'max_revenue' => 10000.00,
                'fee' => 'R$ 197,00'
            ],
            [
                'revenue' => 'R$ 10.001,00 a R$ 20.000,00',
                'min_revenue' => 10001.00,
                'max_revenue' => 20000.00,
                'fee' => 'R$ 247,00'
            ],
            [
                'revenue' => 'R$ 20.001,00 a R$ 30.000,00',
                'min_revenue' => 20001.00,
                'max_revenue' => 30000.00,
                'fee' => 'R$ 497,00'
            ],
            [
                'revenue' => 'R$ 30.001,00 a R$ 50.000,00',
                'min_revenue' => 30001.00,
                'max_revenue' => 50000.00,
                'fee' => 'R$ 997,00'
            ],
            [
                'revenue' => 'R$ 50.001,00 a R$ 70.000,00',
                'min_revenue' => 50001.00,
                'max_revenue' => 70000.00,
                'fee' => 'R$ 1.497,00'
            ],
            [
                'revenue' => 'R$ 70.001,00 a R$ 100.000,00',
                'min_revenue' => 70001.00,
                'max_revenue' => 100000.00,
                'fee' => 'R$ 1.997,00'
            ],
            [
                'revenue' => 'R$ 100.001,00 a R$ 150.000,00',
                'min_revenue' => 100001.00,
                'max_revenue' => 150000.00,
                'fee' => 'R$ 2.997,00'
            ],
            [
                'revenue' => 'Acima de R$ 150.000,00',
                'min_revenue' => 150000.00,
                'max_revenue' => null,
                'fee' => 'R$ 3.997,00'
            ],
        ];

        $fee = $fees[$index];

        // Incrementar o contador
        $index++;

        // Verificar se o contador excede o tamanho do array
        if ($index >= count($fees)) {
            $index = 0; // Recomeçar o ciclo
        }

        return [
            'revenue' => $fee['revenue'],
            'min_revenue' => $fee['min_revenue'],
            'max_revenue' => $fee['max_revenue'],
            'fee' => $fee['fee']
        ];
    }
}
