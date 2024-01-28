<?php


namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Raffle;
use Illuminate\Contracts\Pagination\Paginator;

interface RaffleRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;

    public function getById(int $id): Raffle;

    public function create(array $data): Raffle;

    public function update(int $id, array $data): Raffle;

    public function delete(int $id): int;
}

