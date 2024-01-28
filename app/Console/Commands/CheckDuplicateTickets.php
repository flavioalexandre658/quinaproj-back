<?php

namespace App\Console\Commands;

use App\Interfaces\CampaignRepositoryInterface;
use App\Models\Ticket;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Str;

class CheckDuplicateTickets extends Command
{
    protected $signature = 'campaigns:check-duplicate-tickets';
    protected $description = 'Check Duplicated tickets';

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
        $campaignId = 9;

        foreach ($campaigns as $campaign) {

            $campaignId = $campaign['id'];
            $duplicateTickets = Ticket::where('campaign_id', $campaignId)
                ->select('number', \DB::raw('COUNT(*) as count'))
                ->groupBy('number')
                ->having('count', '>', 1)
                ->get();

            if ($duplicateTickets->count() > 0) {
                // Existem tickets duplicados para o campaign_id = 9
                foreach ($duplicateTickets as $ticket) {
                    $this->info('Duplicated tickets:' . $ticket['number'] . '-' . $campaign['name'] . '-' . $campaign['id']);
                    // Faça algo com os tickets duplicados
                    // Cada $ticket contém a propriedade 'number' e 'count'
                    // 'number' é o número duplicado e 'count' é a contagem de duplicatas
                }
            } else {
                $this->info('Not duplicated tickets found');
                // Não há tickets duplicados para o campaign_id = 9
                // Faça algo, se necessário
            }
        }
    }
}
