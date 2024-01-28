<?php

namespace App\Interfaces;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

interface StatisticsRepositoryInterface
{
    public function getUsersPaymentsStatistics($start_date = null, $end_date = null) : JsonResponse;
}

