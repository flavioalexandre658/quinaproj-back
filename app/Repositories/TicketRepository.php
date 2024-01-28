<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\TicketRepositoryInterface;
use App\Models\Ticket;

class TicketRepository implements TicketRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $ticket = Ticket::filter($filter);
        return $ticket->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Ticket
     * @throws \Exception
     */
    public function getById(int $id): Ticket
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $ticket;
    }

    /**
     * @param array $data
     * @return Ticket
     */
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Ticket
     * @throws \Exception
     */
    public function update(int $id, array $data): Ticket
    {
        $ticket = $this->getById($id);
        $ticket->update($data);
        return $ticket;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Ticket::destroy($id);
    }

    public function getByCampaignId(int $campaignId, int $limit, int $page, RequestFilter $filter): Paginator
    {

        $tickets = Ticket::where('campaign_id', $campaignId)->orderBy('number')->filter($filter);

        if (!$tickets) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return $tickets->paginate($limit, ['*'], 'page', $page);
    }


    public function getByCollaboratorId(int $collaboratorId, int $limit, int $page, RequestFilter $filter): Paginator
    {

        $tickets = Ticket::where('collaborator_id', $collaboratorId)->orderBy('number')->filter($filter);

        if (!$tickets) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return $tickets->paginate($limit, ['*'], 'page', $page);
    }

    public function getByCollaboratorIdPaid(int $collaboratorId, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $tickets = Ticket::where('collaborator_id', $collaboratorId)
            ->whereHas('collaborator', function ($query) {
                $query->where('status_payment', 1);
            })
            ->orderBy('number')
            ->filter($filter);

        if (!$tickets) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return $tickets->paginate($limit, ['*'], 'page', $page);
    }

    public function getByCampaignIdAndTicketNumber(int $campaignId, int $ticketNumber): Ticket
    {
        $ticket = Ticket::with(['collaborator', 'campaign'])
            ->where('campaign_id', $campaignId)
            ->where('number', $ticketNumber)
            ->where('status', '-1')
            ->whereNotNull('collaborator_id') // Verifica se collaborator_id não é nulo
            ->first();

        if (!$ticket) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return $ticket;
    }

}
