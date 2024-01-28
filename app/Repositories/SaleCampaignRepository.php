<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\SaleCampaignRepositoryInterface;
use App\Models\SaleCampaign;
use Illuminate\Http\JsonResponse;

class SaleCampaignRepository implements SaleCampaignRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $saleCampaign = SaleCampaign::filter($filter);
        return $saleCampaign->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return SaleCampaign
     * @throws \Exception
     */
    public function getById(int $id): SaleCampaign
    {
        $saleCampaign = SaleCampaign::find($id);
        if (!$saleCampaign) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $saleCampaign;
    }

    public function getByCampaignId(int $campaignId): JsonResponse
    {

        $saleCampaign = SaleCampaign::with(['sale', 'campaign'])->where('campaign_id', $campaignId)
            //->limit(5)
            ->get();

        if (!$saleCampaign) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($saleCampaign);
    }

    /**
     * @param array $data
     * @return SaleCampaign
     */
    public function create(array $data): SaleCampaign
    {
        return SaleCampaign::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return SaleCampaign
     * @throws \Exception
     */
    public function update(int $id, array $data): SaleCampaign
    {
        $saleCampaign = $this->getById($id);
        $saleCampaign->update($data);
        return $saleCampaign;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return SaleCampaign::destroy($id);
    }

}
