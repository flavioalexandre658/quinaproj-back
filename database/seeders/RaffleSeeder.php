<?php

namespace Database\Seeders;

use App\Models\Raffle;
use Illuminate\Database\Seeder;

class RaffleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Raffle::factory()->count(6)->create();
    }
}
