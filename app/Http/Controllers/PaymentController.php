<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentRepositoryInterface $PaymentRepository;

    public function __construct(PaymentRepositoryInterface $PaymentRepository)
    {
        $this->PaymentRepository = $PaymentRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->PaymentRepository->getAll($limit, $page, $filter);
    }

    public function getByCampaignId(int $campaign_id, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        return $this->PaymentRepository->getByCampaignId($campaign_id, $limit, $page, $filter);
    }

    public function getByCollaboratorId(int $collaborator_id, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        return $this->PaymentRepository->getByCollaboratorId($collaborator_id, $limit, $page, $filter);
    }

    public function getByUserId(int $user_id, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        return $this->PaymentRepository->getByUserId($user_id, $limit, $page, $filter);
    }

    public function getByCampaignAndCollaboratorId(int $campaign_id, int $collaborator_id, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        return $this->PaymentRepository->getByCampaignAndCollaboratorId($campaign_id, $collaborator_id, $limit, $page, $filter);
    }

    public function getByCampaignAndUserId(int $campaign_id, int $user_id, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        return $this->PaymentRepository->getByCampaignAndUserId($campaign_id, $user_id, $limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $payment = $this->PaymentRepository->getById($id);

            return response()->json([$payment]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByTransaction(string $id): JsonResponse
    {
        try {
            $payment = $this->PaymentRepository->getByTransactionId($id);

            return response()->json([$payment]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {

        try {
            $validatedData = $request->validate([
                'transaction_id'        => 'required|string',
                'amount'        => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'currency'        => 'required|string',
                'status'        => 'required|string',
                'collaborator_id'  => 'int|exists:collaborators,id|nullable',
                'campaign_id'  => 'int|exists:campaigns,id|nullable',
                'user_id'  => 'int|exists:users,id|nullable'
            ]);

            $payment = $this->PaymentRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Payment successfully'),
                'payment' => $payment
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {

            $validatedData = $request->validate([
                'transaction_id'        => 'string',
                'amount'        => 'regex:/^\d*(\.\d{2})?$/',
                'currency'        => 'string',
                'status'        => 'string',
                'collaborator_id'  => 'int|exists:collaborators,id|nullable',
                'campaign_id'  => 'int|exists:campaigns,id|nullable',
                'user_id'  => 'int|exists:users,id|nullable'
            ]);

            $payment = $this->PaymentRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Payment successfully'),
                'payment' => $payment
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        try {
            if ($this->PaymentRepository->getById($id)) {
                $this->PaymentRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Payment successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
