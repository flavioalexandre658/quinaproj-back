<?php

namespace Database\Seeders;

use App\Models\TicketFilter;
use Illuminate\Database\Seeder;

class TicketFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketFilter::factory()->count(2)->create();
    }
}
