<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\SaleCampaign;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

interface SaleCampaignRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getByCampaignId(int $id): JsonResponse;
    public function getById(int $id): SaleCampaign;
    public function create(array $data): SaleCampaign;
    public function update(int $id, array $data): SaleCampaign;
    public function delete(int $id): int;
}

