<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(FeeSeeder::class);
        $this->call(TicketFilterSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(RaffleSeeder::class);
        /*
        $this->call(SaleSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(SocialMediaSeeder::class);
        $this->call(AwardSeeder::class);
        $this->call(CategorySeeder::class);
$this->call(TicketFilterSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(FeeSeeder::class);
        $this->call(RaffleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CampaignSeeder::class);
        $this->call(SaleCampaignSeeder::class);
        //$this->call(AwardCampaignSeeder::class);
        $this->call(TicketSeeder::class);*/
        //$this->call(CollaboratorSeeder::class);

    }
}
