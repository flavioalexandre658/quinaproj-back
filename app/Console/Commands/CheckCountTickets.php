<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use App\Models\Campaign;

class CheckCountTickets extends Command
{
    protected $signature = 'campaigns:check-count-tickets';
    protected $description = 'Check the count of tickets per campaign';

    public function handle()
    {
        $campaigns = Campaign::all();

        foreach ($campaigns as $campaign) {
            $campaignId = $campaign->id;
            $ticketCount = Ticket::where('campaign_id', $campaignId)->count();

            $this->info('Campaign ID: ' . $campaignId . ' - Ticket Generated Count: ' . $ticketCount);
        }
    }
}
