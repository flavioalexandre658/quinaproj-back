<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\SocialMedia;
use Illuminate\Contracts\Pagination\Paginator;

interface SocialMediaRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): SocialMedia;
    public function create(array $data): SocialMedia;
    public function update(int $id, array $data): SocialMedia;
    public function delete(int $id): int;
}

