<?php


namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\Paginator;

interface PaymentRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;

    public function getById(int $id): Payment;
    public function getByTransactionId(string $transaction_id): Payment;

    public function create(array $data): Payment;

    public function update(int $id, array $data): Payment;

    public function delete(int $id): int;

    public function getByCampaignId(int $campaign_id, int $limit, int $page, RequestFilter $filter): Paginator;

    public function getByCollaboratorId(int $collaborator_id, int $limit, int $page, RequestFilter $filter): Paginator;

    public function getByUserId(int $user_id, int $limit, int $page, RequestFilter $filter): Paginator;

    public function getByCampaignAndUserId(int $campaign_id, int $user_id, int $limit, int $page, RequestFilter $filter): Paginator;

    public function getByCampaignAndCollaboratorId(int $campaign_id, int $collaborator_id, int $limit, int $page, RequestFilter $filter): Paginator;
}

