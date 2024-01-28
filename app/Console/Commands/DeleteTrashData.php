<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Carbon\Carbon;

class DeleteTrashData extends Command
{
    protected $signature = 'tickets:delete-trash';
    protected $description = 'Delete tickets generated today with status "1"';

    public function handle()
    {/*
        $today = Carbon::now()->toDateString();
        $campaignId = 24; // Substitua pelo ID real da campanha

        // Buscar e excluir tickets gerados hoje com status "1" na campanha específica
        Ticket::whereDate('created_at', $today)
            ->where('status', '-1')
            ->where('collaborator_id', 3184)
            ->where('campaign_id', $campaignId)
            ->delete();

        $this->info('Tickets gerados hoje com status "1" na campanha específica foram deletados com sucesso.');*/
    }

}
