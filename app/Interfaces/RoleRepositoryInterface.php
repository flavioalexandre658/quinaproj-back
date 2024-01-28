<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Role;
use Illuminate\Contracts\Pagination\Paginator;

interface RoleRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Role;
    public function create(array $data): Role;
    public function update(int $id, array $data): Role;
    public function delete(int $id): int;
}

