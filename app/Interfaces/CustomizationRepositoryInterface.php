<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Customization;
use Illuminate\Contracts\Pagination\Paginator;

interface CustomizationRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Customization;
    public function create(array $data): Customization;
    public function update(int $id, array $data): Customization;
    public function delete(int $id): int;
}

