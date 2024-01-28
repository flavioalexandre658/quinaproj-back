<?php

namespace App\Observers;

use App\Interfaces\CampaignRepositoryInterface;
use App\Models\Collaborator;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Campaign;
use App\Events\PaymentStatusUpdated;
class CampaignObserver
{
    private CampaignRepositoryInterface $CampaignRepository;

    public function __construct(CampaignRepositoryInterface $CampaignRepository
    )
    {
        $this->CampaignRepository = $CampaignRepository;
    }
    /**
     * Handle the Payment "created" event.
     */
    public function created(Campaign $campaign): void
    {
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Campaign $campaign): void
    {
        try {

            if($campaign->released_until_fee) {
                /*$day_of_changes = '2024-01-13 00:00:01';
                $digits = strlen((string)($campaign->amount_tickets - 1));

                if($digits < 4 || ($campaign->created_at <  $day_of_changes)) {
                    $ticket = Ticket::where('campaign_id', $campaign->id)->first();
                    if (!$ticket) {
                        $this->CampaignRepository->generateTickets($campaign); // Gera os tickets
                    }
                }else{*/
                    $this->CampaignRepository->generateAndSaveRandomNumbers($campaign); // Gera os tickets
                //}

            }

        } catch (\Exception $e) {
            echo($e->getMessage());
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Campaign $campaign): void
    {
        //
    }
}
