<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\StatisticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    private StatisticsRepositoryInterface $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function getUsersPaymentsStatistics($start_date = null, $end_date = null): JsonResponse
    {
        // Definir as datas padrão se não forem fornecidas
        $start_date = $start_date ?? null;
        $end_date = $end_date ?? null;

        $usersPaymentsStatistics = $this->statisticsRepository->getUsersPaymentsStatistics($start_date, $end_date);

        return response()->json($usersPaymentsStatistics);
    }
}
