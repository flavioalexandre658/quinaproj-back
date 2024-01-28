<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\TicketFilter;
use Illuminate\Contracts\Pagination\Paginator;

interface TicketFilterRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): TicketFilter;
    public function create(array $data): TicketFilter;
    public function update(int $id, array $data): TicketFilter;
    public function delete(int $id): int;
}

