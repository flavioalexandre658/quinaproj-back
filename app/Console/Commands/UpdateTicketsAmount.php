<?php

namespace App\Console\Commands;

use App\Models\Collaborator;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UpdateTicketsAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tickets-amount';

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
        $campaigns = Campaign::with('user')->get();

        foreach ($campaigns as $campaign) {

            $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);
            $filePath = $userFolder . '/' . $campaign->uuid . '.txt';

            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $numbers = explode(',', $content);

                $totalNumbersEnd = count($numbers);
                $campaign->available_tickets = $totalNumbersEnd;

                if ($campaign->pending_tickets) {
                    $totalAmountOfTickets = Collaborator::where('campaign_id', $campaign->id)
                        ->where('status_payment', 0)
                        ->sum('amount_of_tickets');

                    $campaign->pending_tickets = $totalAmountOfTickets;
                }

                $campaign->unavailable_tickets = $campaign->amount_tickets - $totalNumbersEnd;

                $this->updateCampaignTickets($campaign);
            }
        }
    }

    private function updateCampaignTickets($campaign){
        try {
            Campaign::where('id', $campaign->id)->update(['available_tickets' => $campaign->available_tickets, 'pending_tickets' => $campaign->pending_tickets, 'unavailable_tickets' => $campaign->unavailable_tickets]);
            $this->info('Campaign Tickets has been updated. CAMPAIGN: '. $campaign->name .' ID: '. $campaign->id );
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar a campanha: ' . $e->getMessage());
        }
    }
}
