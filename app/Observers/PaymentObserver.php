<?php

namespace App\Observers;

use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Models\Collaborator;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Campaign;
use App\Events\PaymentStatusUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    private CampaignRepositoryInterface $CampaignRepository;
    private CollaboratorRepositoryInterface $CollaboratorRepository;
    public function __construct(CampaignRepositoryInterface $CampaignRepository,
                                CollaboratorRepositoryInterface $CollaboratorRepository
    )
    {
        $this->CampaignRepository = $CampaignRepository;
        $this->CollaboratorRepository = $CollaboratorRepository;
    }
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        //
        try {
            $status = strtolower($payment->status);
            $day_of_changes = '2024-01-13 00:00:01';
            $newStatus = ($status === 'approved' && $payment->user_id !== null && $payment->user_id !== '') ? 1 : 0;

            // Atualizar o status da campanha e gera os tickets
            if ($payment->user_id !== null && $payment->user_id !== '') {
                $campaign = Campaign::where('id', $payment->campaign_id)
                    ->where('user_id', $payment->user_id)
                    ->first();

                if ($campaign) {

                    if($campaign->status != 1) {
                        $campaign->status = $newStatus;

                        if($campaign->status == 1) {
                            $campaign->released_until_fee = 0;
                        }
                        if ($newStatus) {
                            /*$digits = strlen((string)($campaign->amount_tickets - 1));

                            if($digits < 4 || ($campaign->created_at <  $day_of_changes)) {
                                $ticket = Ticket::where('campaign_id', $payment->campaign_id)->first();
                                if (!$ticket) {
                                    $this->CampaignRepository->generateTickets($campaign); // Gera os tickets
                                }
                            }else{*/
                                $this->CampaignRepository->generateAndSaveRandomNumbers($campaign); // Gera os tickets
                            //}
                        }/*else{
                            $digits = strlen((string)($campaign->amount_tickets - 1));

                            if($digits < 4 && ($campaign->created_at <  $day_of_changes)) {
                                $this->CampaignRepository->deleteRandomNumbersFile($campaign); // Gera os tickets
                            }
                        }*/
                        $campaign->save();
                        //event(new PaymentStatusUpdated($payment));
                    }
                }
            }

            // Atualizar os tickets
            if ($payment->collaborator_id !== null && $payment->collaborator_id !== '') {
                if ($payment->isDirty('status')) {

                    $collaborator = Collaborator::where('id', $payment->collaborator_id)->with('campaign')->first();

                    $status_payment_collaborator = '0';

                    if($status == 'approved'){
                        $status_payment_collaborator = '1';
                    }else{
                        if($collaborator->status_payment != '1') {
                            if ($status != 'pending') {
                                $status_payment_collaborator = '-1';
                            }
                        }else{
                            $status_payment_collaborator = $collaborator->status_payment;
                        }
                    }

                    $collaborator->status_payment = $status_payment_collaborator;
                    $collaborator->save();

                    /*$digits = strlen((string)($collaborator->campaign->amount_tickets - 1));

                    if($digits < 4 || ($collaborator->campaign->created_at <  $day_of_changes)) {

                        if($status === 'approved'){
                            $newTicketStatus = '-1';
                        }else if ($status === 'pending'){
                            $newTicketStatus = '0';
                        }else{
                            $newTicketStatus = '-1';
                        }

                        Ticket::where('collaborator_id', $payment->collaborator_id)
                            ->chunk(200, function ($tickets) use ($newTicketStatus) {
                                foreach ($tickets as $ticket) {
                                    $ticket->status = $newTicketStatus;
                                    $ticket->save();
                                }
                            });

                    }else{*/

                        if($status === 'approved'){
                            $this->CollaboratorRepository->reserveNumbers($collaborator);
                        }else{
                            if ($status != 'pending') {
                                $this->CollaboratorRepository->cancelNumbers($collaborator);
                            }
                        }

                   // }

                }
            }
        } catch (\Exception $e) {
            echo($e->getMessage());
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        try {
            $status = strtolower($payment->status);
            $day_of_changes = '2024-01-13 00:00:01';
            $newStatus = ($status === 'approved' && $payment->user_id !== null && $payment->user_id !== '') ? 1 : 0;

            // Atualizar o status da campanha e gera os tickets
            if ($payment->user_id !== null && $payment->user_id !== '') {
                $campaign = Campaign::where('id', $payment->campaign_id)
                    ->where('user_id', $payment->user_id)
                    ->first();

                if ($campaign) {

                    if($campaign->status != 1) {
                        $campaign->status = $newStatus;
                        if($campaign->status == 1) {
                            $campaign->released_until_fee = 0;
                        }

                        if ($newStatus) {
                           /* $digits = strlen((string)($campaign->amount_tickets - 1));

                            if($digits < 4 || ($campaign->created_at < $day_of_changes)) {

                                $ticket = Ticket::where('campaign_id', $payment->campaign_id)->first();
                                if (!$ticket) {
                                    $this->CampaignRepository->generateTickets($campaign); // Gera os tickets
                                }
                            }else{*/
                                $this->CampaignRepository->generateAndSaveRandomNumbers($campaign); // Gera os tickets
                            //}
                        }/*else{
                            $digits = strlen((string)($campaign->amount_tickets - 1));

                            if($digits < 4 && ($campaign->created_at <  $day_of_changes)) {
                                $this->CampaignRepository->deleteRandomNumbersFile($campaign); // Gera os tickets
                            }
                        }*/
                        $campaign->save();
                        //event(new PaymentStatusUpdated($payment));
                    }
                }
            }

            // Atualizar os tickets
            if ($payment->collaborator_id !== null && $payment->collaborator_id !== '') {
                if ($payment->isDirty('status')) {

                    $collaborator = Collaborator::where('id', $payment->collaborator_id)->with('campaign')->first();

                    $status_payment_collaborator = '0';

                    if($status == 'approved'){
                        $status_payment_collaborator = '1';
                    }else{
                        if($collaborator->status_payment != '1') {
                            if ($status != 'pending') {
                                $status_payment_collaborator = '-1';
                            }
                        }else{
                            $status_payment_collaborator = $collaborator->status_payment;
                        }
                    }

                    $collaborator->status_payment = $status_payment_collaborator;
                    $collaborator->save();


                    /*$digits = strlen((string)($collaborator->campaign->amount_tickets - 1));

                    if($digits < 4 || ($collaborator->campaign->created_at <  $day_of_changes)) {

                        if($status === 'approved'){
                            $newTicketStatus = '-1';
                        }else if ($status === 'pending'){
                            $newTicketStatus = '0';
                        }else{
                            $newTicketStatus = '-1';
                        }

                        Ticket::where('collaborator_id', $payment->collaborator_id)
                            ->chunk(200, function ($tickets) use ($newTicketStatus) {
                                foreach ($tickets as $ticket) {
                                    $ticket->status = $newTicketStatus;
                                    $ticket->save();
                                }
                            });

                    }else{*/

                        if($status_payment_collaborator === '1'){
                            $this->CollaboratorRepository->reserveNumbers($collaborator);
                        }else if ($status_payment_collaborator === '-1'){
                            $this->CollaboratorRepository->cancelNumbers($collaborator);
                        }

                   // }

                }
            }
        } catch (\Exception $e) {
            echo($e->getMessage());
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
