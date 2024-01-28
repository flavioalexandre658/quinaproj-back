<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

interface UserRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter, ?string $start_date = null, ?string $end_date = null): Paginator;
    public function getById(int $id): User;
    public function getByUuid(string $uuid): User;
    public function getByEmail(string $email): User;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function delete(int $id): int;
    public function getCampaignByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter, ?string $phone = null, ?string $tickets_req = null, ?string $start_date = null, ?string $end_date = null): Paginator;
    public function getCustomizationByUserUuid(string $uuid): JsonResponse;
    public function getSocialMediaByUserUuid(string $uuid): JsonResponse;
    public function getPaymentMethodByUserUuid(string $uuid): JsonResponse;
    public function getPaymentMethodByUserId(int $id): JsonResponse;
    public function activeAccount($token) : JsonResponse;
    public function resetPassword(string $uuid, array $data): User;
}

