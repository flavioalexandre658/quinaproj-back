<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\AwardCampaign;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

interface AwardCampaignRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): AwardCampaign;
    public function getByCampaignId(int $id): JsonResponse;
    public function create(array $data): AwardCampaign;
    public function update(int $id, array $data): AwardCampaign;
    public function delete(int $id): int;
}

