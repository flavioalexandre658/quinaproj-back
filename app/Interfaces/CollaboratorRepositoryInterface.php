<?php


namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Collaborator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

interface CollaboratorRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;

    public function getById(int $id): Collaborator;
    public function getByUuid(string $uuid): Collaborator;
    public function create(array $data): Collaborator;

    public function update(int $id, array $data): Collaborator;

    public function getTicketsCollaborators(int $campaign_id, int $status): JsonResponse;

    public function delete(int $id): int;

    public function reserveNumbers($collaborator);
    public function re_reserveNumbers($collaborator);
    public function cancelNumbers($collaborator);

    public function getCollaboratorsByIntervalDate(int $campaign_id, string $start_date, string $end_date): JsonResponse;

    public function getCollaboratorsByCampaignId(int $campaign_id, int $limit, int $page, RequestFilter $filter, ?string $phone = null, ?string $tickets_req = null, ?string $start_date = null, ?string $end_date = null): Paginator;
}

