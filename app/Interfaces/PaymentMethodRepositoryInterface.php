<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\Paginator;

interface PaymentMethodRepositoryInterface
{
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator;
    public function getById(int $id): PaymentMethod;
    public function create(array $data): PaymentMethod;
    public function update(int $id, array $data): PaymentMethod;
    public function delete(int $id): int;
}

