<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\TicketFilterRepositoryInterface;
use App\Models\TicketFilter;

class TicketFilterRepository implements TicketFilterRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $ticketFilter = TicketFilter::filter($filter);
        return $ticketFilter->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return TicketFilter
     * @throws \Exception
     */
    public function getById(int $id): TicketFilter
    {
        $ticketFilter = TicketFilter::find($id);
        if (!$ticketFilter) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $ticketFilter;
    }

    /**
     * @param array $data
     * @return TicketFilter
     */
    public function create(array $data): TicketFilter
    {
        return TicketFilter::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return TicketFilter
     * @throws \Exception
     */
    public function update(int $id, array $data): TicketFilter
    {
        $ticketFilter = $this->getById($id);
        $ticketFilter->update($data);
        return $ticketFilter;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return TicketFilter::destroy($id);
    }

}
