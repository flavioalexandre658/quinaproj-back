<?php

namespace App\Console\Commands;

use App\Interfaces\CollaboratorRepositoryInterface;
use App\Models\Campaign;
use App\Models\Collaborator;
use Illuminate\Console\Command;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckTicketExpiration extends Command
{
    protected $signature = 'check:ticket-expiration';

    protected $description = 'Check ticket expiration and update status accordingly';

    private CollaboratorRepositoryInterface $CollaboratorRepository;

    public function __construct(CollaboratorRepositoryInterface $CollaboratorRepository
    )
    {
        parent::__construct();
        $this->CollaboratorRepository = $CollaboratorRepository;
    }
    public function handle()
    {
        $now = Carbon::now('America/Sao_Paulo');

        $expiredCollaborators = Collaborator::where([
            ['expire_date', '<=', $now],
            ['status_payment', '=', 0],
        ])->with('campaign', 'campaign.user')->get();

        foreach ($expiredCollaborators as $collaborator) {
                // Atualiza o colaborador diretamente no banco de dados
                $collaborator->update(['status_payment' => '-1']);
                $this->CollaboratorRepository->cancelNumbers($collaborator);
                $this->info('Collaborator cancelled orders after expiration COLLAB: '.$collaborator->name.' ID: '.$collaborator->id);
        }
    }
}
