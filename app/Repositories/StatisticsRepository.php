<?php


namespace App\Repositories;

use App\Helpers\ImageHelper;
use App\Interfaces\StatisticsRepositoryInterface;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;


class StatisticsRepository implements StatisticsRepositoryInterface
{

    private ImageHelper $imageHelper;

    public function __construct(
        ImageHelper $imageHelper
    )
    {
        $this->imageHelper = $imageHelper;
    }


    public function getUsersPaymentsStatistics($start_date = null, $end_date = null): JsonResponse
    {
        // Definir as datas padrão se não forem fornecidas
        $start_date = $start_date ?? Carbon::now()->startOfWeek();
        $end_date = $end_date ?? Carbon::now();

        // USER

        // Obter a quantidade total de usuários
        $totalUsers = User::count();

        // Obter a quantidade de usuários cadastrados no período atual
        $currentPeriodUsers = User::whereBetween('created_at', [$start_date, $end_date])->count();

        // Obter a quantidade de usuários cadastrados no período anterior
        $previousPeriodUsers = User::whereBetween('created_at', [Carbon::parse($start_date)->subWeek()->startOfWeek(), Carbon::parse($end_date)->subWeek()->endOfWeek()])->count();

        // Calcular a porcentagem de crescimento ou decrescimento
        $userPercentageChange = 0;

        if ($previousPeriodUsers !== 0) {
            $userPercentageChange = (($currentPeriodUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100;
        }

        // Determinar o status da porcentagem
        $userStatus = $userPercentageChange > 0 ? 'crescimento' : ($userPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // PAYMENT

        // Obter os pagamentos no intervalo de datas
        $payments = Payment::whereBetween('created_at', [$start_date, $end_date])->get();

        // Quantidade de pagamentos com user_id diferente de null
        $numberOfPaymentsWithUser = $payments->whereNotNull('user_id')->count();

        // Quantidade de pagamentos com status 'approved'
        $numberOfPaymentsWithStatusApproved = $payments->whereNotNull('user_id')->where('status', 'approved')->count();

        // Montante somado dos pagamentos com user_id
        $totalAmount = $payments->whereNotNull('user_id')->sum('amount');

        // Montante somado dos pagamentos com status 'approved'
        $approvedAmount = $payments->whereNotNull('user_id')->where('status', 'approved')->sum('amount');

        // Montante somado dos pagamentos com status 'cancelled' ou 'rejected'
        $cancelledRejectedAmount = $payments->whereNotNull('user_id')->whereIn('status', ['cancelled', 'rejected'])->sum('amount');

        // Montante somado dos pagamentos com status 'pending'
        $pendingAmount = $payments->whereNotNull('user_id')->where('status', 'pending')->sum('amount');

        // Obter a quantidade de pagamentos no período anterior
        $previousPayments = Payment::whereBetween('created_at', [Carbon::parse($start_date)->subWeek()->startOfWeek(), Carbon::parse($end_date)->subWeek()->endOfWeek()])->get();

        // Quantidade de pagamentos com status 'approved'
        $previousNumberOfPaymentsWithStatusApproved = $previousPayments->whereNotNull('user_id')->where('status', 'approved')->count();

        // Quantidade de pagamentos com user_id diferente de null
        $numberOfPaymentsWithUserPrevious = $previousPayments->whereNotNull('user_id')->count();

        // Montante somado dos pagamentos no período anterior
        $previousTotalAmount = $previousPayments->whereNotNull('user_id')->sum('amount');

        // Montante somado dos pagamentos com status 'approved' no período anterior
        $previousApprovedPayments = $previousPayments->whereNotNull('user_id')->where('status', 'approved')->sum('amount');

        // Montante somado dos pagamentos com status 'pending' no período anterior
        $previousPendingPayments = $previousPayments->whereNotNull('user_id')->where('status', 'pending')->sum('amount');

        // Montante somado dos pagamentos com status 'cancelled' ou 'rejected' no período anterior
        $previousCancelledRejectedAmount = $previousPayments->whereNotNull('user_id')->whereIn('status', ['cancelled', 'rejected'])->sum('amount');

        $userPercentageChange = 0;

        if ($previousPeriodUsers !== 0) {
            $userPercentageChange = (($currentPeriodUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100;
        }

        // Determinar o status da porcentagem
        $userStatus = $userPercentageChange > 0 ? 'crescimento' : ($userPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        $paymentPercentageChange = 0;

        if ($previousTotalAmount !== 0) {
            $paymentPercentageChange = (($totalAmount - $previousTotalAmount) / $previousTotalAmount) * 100;
        }

        // Calcular a porcentagem de crescimento ou decrescimento para o approvedAmount
        $approvedPercentageChange = 0;

        if ($previousApprovedPayments !== 0 && $previousApprovedPayments !== null) {
            $approvedPercentageChange = (($approvedAmount - $previousApprovedPayments) / abs($previousApprovedPayments)) * 100;
        }

        // Calcular a relação entre numberOfPayments e numberOfPaymentsWithStatusApproved (em porcentagem)
        $conversionRate = 0;

        if ($numberOfPaymentsWithUser !== 0 && $numberOfPaymentsWithStatusApproved !== null) {
            $conversionRate = ($numberOfPaymentsWithStatusApproved / $numberOfPaymentsWithUser) * 100;
        }

        // Calcular a relação entre numberOfPayments e numberOfPaymentsWithStatusApproved (em porcentagem)
        $previousConversionRate = 0;

        if ($numberOfPaymentsWithUserPrevious !== 0 && $previousNumberOfPaymentsWithStatusApproved !== null) {
            $previousConversionRate = ($previousNumberOfPaymentsWithStatusApproved / $numberOfPaymentsWithUserPrevious) * 100;
        }

        // Calcular a porcentagem de crescimento ou decrescimento para o conversionRate
        $conversionRatePercentageChange = 0;

        if ($previousApprovedPayments !== 0 && $previousApprovedPayments !== null) {
            if ($previousConversionRate !== 0) {
                $conversionRatePercentageChange = (($conversionRate - $previousConversionRate) / abs($previousConversionRate)) * 100;
            }
        }

        // Calcular a porcentagem de crescimento ou decrescimento para o pendingAmount
        $pendingPercentageChange = 0;

        if ($previousPendingPayments !== 0 && $previousPendingPayments !== null) {
            $pendingPercentageChange = (($pendingAmount - $previousPendingPayments) / abs($previousPendingPayments)) * 100;
        }

        // Calcular a porcentagem de crescimento ou decrescimento para o cancelledRejectedAmount
        $cancelledRejectedPercentageChange = 0;

        if ($previousCancelledRejectedAmount !== 0 && $previousCancelledRejectedAmount !== null) {
            $cancelledRejectedPercentageChange = (($cancelledRejectedAmount - $previousCancelledRejectedAmount) / abs($previousCancelledRejectedAmount)) * 100;
        }

        // Calcular a porcentagem de crescimento ou decrescimento para NumberOfPaymentsWithUser
        $numberOfPaymentsWithUserPercentageChange = 0;

        if ($numberOfPaymentsWithUserPrevious !== 0) {
            $numberOfPaymentsWithUserPercentageChange = (($numberOfPaymentsWithUser - $numberOfPaymentsWithUserPrevious) / $numberOfPaymentsWithUserPrevious) * 100;
        }

        // Calcular a porcentagem de crescimento ou decrescimento para previousNumberOfPaymentsWithStatusApproved
        $previousNumberOfPaymentsWithStatusApprovedPercentageChange = 0;

        if ($previousNumberOfPaymentsWithStatusApproved !== 0) {
            $previousNumberOfPaymentsWithStatusApprovedPercentageChange = (($numberOfPaymentsWithStatusApproved - $previousNumberOfPaymentsWithStatusApproved) / $previousNumberOfPaymentsWithStatusApproved) * 100;
        }

        // Determinar o status da porcentagem
        $paymentStatus = $paymentPercentageChange > 0 ? 'crescimento' : ($paymentPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem
        $conversionRateStatus = $conversionRatePercentageChange > 0 ? 'crescimento' : ($conversionRatePercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem
        $approvedAmountStatus = $approvedPercentageChange > 0 ? 'crescimento' : ($approvedPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem
        $pendingAmountStatus = $pendingPercentageChange > 0 ? 'crescimento' : ($pendingPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem
        $cancelledRejectedAmountStatus = $cancelledRejectedPercentageChange > 0 ? 'crescimento' : ($cancelledRejectedPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem para NumberOfPaymentsWithUser
        $numberOfPaymentsWithUserStatus = $numberOfPaymentsWithUserPercentageChange > 0 ? 'crescimento' : ($numberOfPaymentsWithUserPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        // Determinar o status da porcentagem para previousNumberOfPaymentsWithStatusApproved
        $previousNumberOfPaymentsWithStatusApprovedStatus = $previousNumberOfPaymentsWithStatusApprovedPercentageChange > 0 ? 'crescimento' : ($previousNumberOfPaymentsWithStatusApprovedPercentageChange < 0 ? 'decrescimento' : 'sem alteração');

        return response()->json([
            'user_statistics' => [
                'total_users' => $totalUsers,
                'period_users' => [
                    'current' => $currentPeriodUsers,
                    'previous' => $previousPeriodUsers,
                ],
                'percentage_change' => $userPercentageChange,
                'status' => $userStatus,
            ],
            'payment_statistics' => [
                'number_of_payments' => [
                    'total' => $numberOfPaymentsWithUser,
                    'approved' => $numberOfPaymentsWithStatusApproved,
                    'previous_approved' => $previousNumberOfPaymentsWithStatusApproved,
                    'previous_total' => $numberOfPaymentsWithUserPrevious,
                    'percentage_change' => $numberOfPaymentsWithUserPercentageChange,
                    'status' => $numberOfPaymentsWithUserStatus,
                    'status_approved' => $previousNumberOfPaymentsWithStatusApprovedStatus,
                    'percentage_change_approved' => $previousNumberOfPaymentsWithStatusApprovedPercentageChange
                ],
                'amounts' => [
                    'total' => $totalAmount,
                    'previous_total' => $previousTotalAmount,
                    'approved' => $approvedAmount,
                    'previous_approved' => $previousApprovedPayments,
                    'cancelled_rejected' => $cancelledRejectedAmount,
                    'pending' => $pendingAmount,
                    'previous_pending' => $previousPendingPayments,
                    'previous_cancelled_rejected' => $previousCancelledRejectedAmount,
                ],
                'conversion_rate' => [
                    'current' => $conversionRate,
                    'previous' => $previousConversionRate,
                    'percentage_change' => $conversionRatePercentageChange,
                    'status' => $conversionRateStatus,
                ],
                'approved_amount' => [
                    'percentage_change' => $approvedPercentageChange,
                    'status' => $approvedAmountStatus,
                ],
                'pending_amount' => [
                    'percentage_change' => $pendingPercentageChange,
                    'status' => $pendingAmountStatus,
                ],
                'cancelled_rejected_amount' => [
                    'percentage_change' => $cancelledRejectedPercentageChange,
                    'status' => $cancelledRejectedAmountStatus,
                ],
                'total_amount' => [
                    'percentage_change' => $paymentPercentageChange,
                    'status' => $paymentStatus,
                ]
            ],
        ]);
    }





}
