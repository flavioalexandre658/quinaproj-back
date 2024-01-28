<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Campaign;
use Illuminate\Contracts\Pagination\Paginator;

interface CampaignRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): Campaign;
    public function getByUuid(string $uuid): Campaign;
    public function getBySlug(string $slug): Campaign;
    public function create(array $data): Campaign;
    public function update(int $id, array $data): Campaign;
    public function generateTickets($campaign);
    public function generateAndSaveRandomNumbers($campaign);
    public function re_generateAndSaveRandomNumbers($campaign);
    public function deleteRandomNumbersFile($campaign);
    public function delete(int $id): int;
}

