<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\AwardCampaignRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AwardCampaignController extends Controller
{
    private AwardCampaignRepositoryInterface $AwardCampaignRepository;

    public function __construct(AwardCampaignRepositoryInterface $AwardCampaignRepository)
    {
        $this->AwardCampaignRepository = $AwardCampaignRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->AwardCampaignRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $awardCampaign = $this->AwardCampaignRepository->getById($id);

            return response()->json([$awardCampaign]);
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

            $awardCampaign = $this->AwardCampaignRepository->getByCampaignId($id);

            return response()->json([$awardCampaign]);
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
                'award_id'        => 'required|int|exists:awards,id',
                'campaign_id'        => 'required|int|exists:campaigns,id',
                'position'        => 'required|int'
            ]);

            $awardCampaign = $this->AwardCampaignRepository->create($validatedData);

            return response()->json([
                'message' => __('Created AwardCampaign successfully'),
                'awardCampaign' => $awardCampaign
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
                'award_id'        => 'int|exists:awards,id',
                'campaign_id'        => 'int|exists:campaigns,id',
                'collaborator_id'    => 'int|exists:collaborators,id|nullable',
                'sorted_number'    => 'string|max:100|nullable',
                'position'        => 'int'
            ]);

            $awardCampaign = $this->AwardCampaignRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated AwardCampaign successfully'),
                'awardCampaign' => $awardCampaign
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
            if ($this->AwardCampaignRepository->getById($id)) {
                $this->AwardCampaignRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted AwardCampaign successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
