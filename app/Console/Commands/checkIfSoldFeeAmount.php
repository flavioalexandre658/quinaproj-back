<?php

namespace App\Console\Commands;

use App\Interfaces\CampaignRepositoryInterface;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Str;

class CheckIfSoldFeeAmount extends Command
{
    protected $signature = 'campaigns:check-fee';
    protected $description = 'check if sold the fee amount';

    private CampaignRepositoryInterface $CampaignRepository;

    public function __construct(CampaignRepositoryInterface $CampaignRepository
    )
    {
        parent::__construct();
        $this->CampaignRepository = $CampaignRepository;
    }

    public function handle()
    {
        $campaigns = Campaign::with(['fee'])->get();

        foreach ($campaigns as $campaign){

            if(!$campaign->status && !$campaign->closed && $campaign->released_until_fee ){

                $fee = (float) str_replace(',', '.', preg_replace('/[^\d,]/', '', $campaign['fee']['fee']));

                // Remove tudo que não é dígito, vírgula ou ponto
                $cleaned_value = preg_replace('/[^\d.,]/', '', $campaign['price_each_ticket']);

                // Substitui a vírgula por ponto, se existir mais de um ponto, mantém o último
                $cleaned_value = preg_replace('/\.(?=.*\.)/', '', str_replace(',', '.', $cleaned_value));

                // Converte para float
                $price_each_ticket = (float) $cleaned_value;

                // Calcular o número que, quando multiplicado por $price_each_ticket, resultará em $fee
                $number_tickets = $fee / $price_each_ticket;

                // Ajustar para ser 10% maior e arredondar para o inteiro mais próximo
                $amountTicketsAllowed = ceil($number_tickets * 1.1);

                $tickets_sold = $campaign['unavailable_tickets'];

                if($amountTicketsAllowed <= $tickets_sold){
                    $this->CampaignRepository->update($campaign['id'], ['released_until_fee' => 0]);
                    $this->info('Check if sold the fee amount executed with success - CAMPAIGN:'. $campaign['name'] . ' - ID: '. $campaign['id']. ' TICKETS SOLD: '. $tickets_sold . ' TICKETS_ALLOWRD: '. $amountTicketsAllowed);
                }else{
                    $this->info('Amount allowed '.$price_each_ticket.' minus that tickets '.$campaign['price_each_ticket'].' sold - CAMPAIGN:'. $campaign['name'] . ' - ID: '. $campaign['id'].' TICKETS SOLD: '. $tickets_sold . ' TICKETS_ALLOWRD: '. $amountTicketsAllowed. ' FEE: '. $fee);
                }

            }
        }
    }
}
