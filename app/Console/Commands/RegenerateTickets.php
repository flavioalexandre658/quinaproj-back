<?php

namespace App\Console\Commands;

use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Models\Collaborator;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegenerateTickets extends Command
{
    protected $signature = 'campaigns:regenerate-tickets';
    protected $description = 'Generate campaigns with new method';


    private CampaignRepositoryInterface $CampaignRepository;
    private CollaboratorRepositoryInterface $CollaboratorRepository;

    public function __construct(CampaignRepositoryInterface $CampaignRepository,
                                CollaboratorRepositoryInterface $CollaboratorRepository
    )
    {
        parent::__construct();
        $this->CampaignRepository = $CampaignRepository;
        $this->CollaboratorRepository = $CollaboratorRepository;
    }

    public function handle()
    {
        $campaigns = Campaign::where('status', 1)->get();
        foreach ($campaigns as $campaign) {
            $gen = $this->CampaignRepository->re_generateAndSaveRandomNumbers($campaign);

            if ($gen) {
                $this->info('Tickets for new method campaign generated. CAMPAIGN: ' . $campaign->name . ' ID: ' . $campaign->id);
                $batchSize = 100;
                Collaborator::where('status_payment', 1)
                    ->where('campaign_id', $campaign->id)
                    ->with(['tickets' => function ($query) {
                        $query->where('status', 'like', '-1%');
                    }, 'campaign', 'campaign.user'])
                    ->chunk($batchSize, function ($collaboratorBatch) use($campaign){
                        foreach ($collaboratorBatch as $collaborator) {
                            // Busque os tickets do colaborador em lotes menores
                            $collaborator->load('tickets:number,collaborator_id');

                            if (sizeof($collaborator->tickets)) {
                                // Extrair os números
                                $numbersArray = array_column($collaborator->tickets->toArray(), 'number');

                                // Obter a quantidade de dígitos desejada
                                $desiredDigits = $this->getDigits($campaign->amount_tickets);

                                // Ajustar os números para ter a quantidade correta de dígitos
                                foreach ($numbersArray as &$number) {
                                    // Adicionar zeros à esquerda se necessário
                                    $number = str_pad($number, $desiredDigits, '0', STR_PAD_LEFT);
                                }

                                // Criar a string
                                $numbersString = implode(',', $numbersArray);

                                $collaborator->numbers = $numbersString;

                                $this->CollaboratorRepository->re_reserveNumbers($collaborator);
                                $this->info('Tickets for new method campaign generated. COLLAB: ' . $collaborator->name . ' ID: ' . $collaborator->id);
                            }

                            // Limpe a relação para evitar problemas de memória
                            //$collaborator->unsetRelation('tickets');
                        }
                    });

            }

        }
    }

    private function getDigits($number)
    {
        return strlen((string)$number) - 1;
    }

    private function updateCollaboratorNumbers($collaborator, $retrievedNumbers){
        try {
            Collaborator::where('id', $collaborator->id)->update(['numbers' => $retrievedNumbers ? implode(',', $retrievedNumbers) : null]);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar colaborador: ' . $e->getMessage());
        }
    }
}
