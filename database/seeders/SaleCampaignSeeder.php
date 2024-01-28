<?php

namespace Database\Seeders;

use App\Models\SaleCampaign;
use Illuminate\Database\Seeder;

class SaleCampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SaleCampaign::factory()->count(10)->create();
    }
}
