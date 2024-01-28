<?php


namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Fee;
use Illuminate\Contracts\Pagination\Paginator;

interface FeeRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;

    public function getById(int $id): Fee;

    public function create(array $data): Fee;

    public function update(int $id, array $data): Fee;

    public function delete(int $id): int;
}

