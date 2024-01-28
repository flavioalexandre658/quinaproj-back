<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Category;
use Illuminate\Contracts\Pagination\Paginator;

interface CategoryRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Category;
    public function create(array $data): Category;
    public function update(int $id, array $data): Category;
    public function delete(int $id): int;
}

