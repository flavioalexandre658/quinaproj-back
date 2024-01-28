<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Ticket;
use Illuminate\Contracts\Pagination\Paginator;

interface TicketRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Ticket;
    public function getByCampaignIdAndTicketNumber(int $campaignId, int $ticketNumber): Ticket;
    public function create(array $data): Ticket;
    public function update(int $id, array $data): Ticket;
    public function delete(int $id): int;
    public function getByCampaignId(int $campaignId, int $limit, int $page, RequestFilter $filter): Paginator;
    public function getByCollaboratorId(int $collaboratorId, int $limit, int $page, RequestFilter $filter): Paginator;
    public function getByCollaboratorIdPaid(int $collaboratorId, int $limit, int $page, RequestFilter $filter): Paginator;
}

