<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\SaleCampaignRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleCampaignController extends Controller
{
    private SaleCampaignRepositoryInterface $SaleCampaignRepository;

    public function __construct(SaleCampaignRepositoryInterface $SaleCampaignRepository)
    {
        $this->SaleCampaignRepository = $SaleCampaignRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->SaleCampaignRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $saleCampaign = $this->SaleCampaignRepository->getById($id);

            return response()->json([$saleCampaign]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByCampaignId(int $id): JsonResponse
    {
        try {

            $saleCampaign = $this->SaleCampaignRepository->getByCampaignId($id);

            return response()->json([$saleCampaign]);
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
                'sale_id'        => 'required|int|exists:sales,id',
                'campaign_id'        => 'required|int|exists:campaigns,id'
            ]);

            $saleCampaign = $this->SaleCampaignRepository->create($validatedData);

            return response()->json([
                'message' => __('Created SaleCampaign successfully'),
                'saleCampaign' => $saleCampaign
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
                'sale_id'        => 'int|exists:sales,id',
                'campaign_id'        => 'int|exists:campaigns,id'
            ]);

            $saleCampaign = $this->SaleCampaignRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated SaleCampaign successfully'),
                'saleCampaign' => $saleCampaign
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
            if ($this->SaleCampaignRepository->getById($id)) {
                $this->SaleCampaignRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted SaleCampaign successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
