<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Award;
use Illuminate\Contracts\Pagination\Paginator;

interface AwardRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Award;
    public function create(array $data): Award;
    public function update(int $id, array $data): Award;
    public function delete(int $id): int;
    public function getByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter): Paginator;
}

