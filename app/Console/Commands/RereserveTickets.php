<?php

namespace App\Console\Commands;

use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Models\Collaborator;
use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Str;

class RereserveTickets extends Command
{
    protected $signature = 'collaborators:rereserve-tickets';
    protected $description = 'Generate campaigns with new method';


    private CollaboratorRepositoryInterface $CollaboratorRepository;

    public function __construct(CollaboratorRepositoryInterface $CollaboratorRepository
    )
    {
        parent::__construct();
        $this->CollaboratorRepository = $CollaboratorRepository;
    }

    public function handle()
    {
        $batchSize = 100; // Defina o tamanho do lote conforme necessário

        Collaborator::where('status_payment', 1)
            ->where('amount_of_tickets', '<=', 4000)
            ->whereNull('numbers')
            ->with(['tickets:number,collaborator_id', 'campaign', 'campaign.user'])
            ->chunk($batchSize, function ($collaboratorBatch) {
                foreach ($collaboratorBatch as $collaborator) {
                    // Busque os tickets do colaborador em lotes menores
                    $collaborator->load('tickets:number,collaborator_id');

                    if (sizeof($collaborator->tickets)) {
                        // Extrair os números
                        $numbersArray = array_column($collaborator->tickets->toArray(), 'number');

                        // Criar a string
                        $numbersString = implode(',', $numbersArray);

                        $collaborator->numbers = $numbersString;

                        $this->CollaboratorRepository->reserveNumbers($collaborator);
                        $this->info('Tickets for new method campaign generated. COLLAB: ' . $collaborator->name .' ID: ' . $collaborator->id);
                    }

                    // Limpe a relação para evitar problemas de memória
                    //$collaborator->unsetRelation('tickets');
                }
            });





    }
}
