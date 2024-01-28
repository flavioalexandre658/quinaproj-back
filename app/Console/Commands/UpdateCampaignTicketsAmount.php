<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;

class UpdateCampaignTicketsAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:campaign-tickets-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update campaign ticket counts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Recupere todas as campanhas
        $campaigns = Campaign::all();

        foreach ($campaigns as $campaign) {

            if(($campaign['status'] && !$campaign['closed']) || ($campaign['released_until_fee'] && !$campaign['closed'])) {
                $this->info('Generate tickets executed with success - CAMPAIGN:'. $campaign['name'] . ' - ID: '. $campaign['id']. ' - STATUS: '. $campaign['status'] . ' - RELEASED: '. $campaign['released_until_fee']);
                $ticketCounts = $campaign->tickets()
                    ->selectRaw('SUM(CASE WHEN status = "1" THEN 1 ELSE 0 END) as available_tickets')
                    ->selectRaw('SUM(CASE WHEN status = "0" THEN 1 ELSE 0 END) as pending_tickets')
                    ->selectRaw('SUM(CASE WHEN status = "-1" THEN 1 ELSE 0 END) as unavailable_tickets')
                    ->first();

                $campaign->update([
                    'available_tickets' => $ticketCounts->available_tickets ?? 0,
                    'pending_tickets' => $ticketCounts->pending_tickets ?? 0, // Defina um valor padrão de 0 para 'pending_tickets'
                    'unavailable_tickets' => $ticketCounts->unavailable_tickets ?? 0, // Defina um valor padrão de 0 para 'unavailable_tickets'
                ]);
            }
        }
    }

}
