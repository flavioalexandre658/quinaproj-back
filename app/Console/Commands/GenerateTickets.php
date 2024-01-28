<?php

namespace App\Console\Commands;

use App\Interfaces\CampaignRepositoryInterface;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Str;

class GenerateTickets extends Command
{
    protected $signature = 'campaigns:generate-tickets';
    protected $description = 'Generate tickets';

    private CampaignRepositoryInterface $CampaignRepository;

    public function __construct(CampaignRepositoryInterface $CampaignRepository
    )
    {
        parent::__construct();
        $this->CampaignRepository = $CampaignRepository;
    }

    public function handle()
    {
        $campaigns = Campaign::all();
        foreach ($campaigns as $campaign){

            if($campaign->status && !$campaign->closed && $campaign->visible){

                $digits = strlen((string)$campaign->amount_tickets) - 1;

                if($digits >= 5) {
                    $this->CampaignRepository->generateTickets($campaign);
                    $this->info('Generate tickets executed with success - CAMPAIGN:'. $campaign['name'] . ' - ID: '. $campaign['id']);
                }
            }
        }
    }
}
