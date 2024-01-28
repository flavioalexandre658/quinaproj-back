<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\Paginator;

interface SaleRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Sale;
    public function create(array $data): Sale;
    public function update(int $id, array $data): Sale;
    public function delete(int $id): int;
    public function getByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter): Paginator;
}

