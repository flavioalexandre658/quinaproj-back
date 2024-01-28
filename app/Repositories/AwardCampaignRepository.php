<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\AwardCampaignRepositoryInterface;
use App\Models\AwardCampaign;
use Illuminate\Http\JsonResponse;

class AwardCampaignRepository implements AwardCampaignRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $awardCampaign = AwardCampaign::filter($filter);
        return $awardCampaign->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return AwardCampaign
     * @throws \Exception
     */
    public function getById(int $id): AwardCampaign
    {
        $awardCampaign = AwardCampaign::find($id);
        if (!$awardCampaign) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $awardCampaign;
    }

    public function getByCampaignId(int $campaignId): JsonResponse
    {

        $awardCampaign = AwardCampaign::with('campaign', 'collaborator', 'award')->where('campaign_id', $campaignId)
            //->limit(5)
            ->get();

        if (!$awardCampaign) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($awardCampaign);
    }
    /**
     * @param array $data
     * @return AwardCampaign
     */
    public function create(array $data): AwardCampaign
    {
        return AwardCampaign::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return AwardCampaign
     * @throws \Exception
     */
    public function update(int $id, array $data): AwardCampaign
    {
        $awardCampaign = $this->getById($id);
        $awardCampaign->update($data);
        return $awardCampaign;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return AwardCampaign::destroy($id);
    }

}
