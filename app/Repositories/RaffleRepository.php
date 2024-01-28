<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\RaffleRepositoryInterface;
use App\Models\Raffle;

class RaffleRepository implements RaffleRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $raffle = Raffle::filter($filter);
        return $raffle->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Raffle
     * @throws \Exception
     */
    public function getById(int $id): Raffle
    {
        $raffle = Raffle::find($id);
        if (!$raffle) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $raffle;
    }

    /**
     * @param array $data
     * @return Raffle
     */
    public function create(array $data): Raffle
    {
        return Raffle::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Raffle
     * @throws \Exception
     */
    public function update(int $id, array $data): Raffle
    {
        $raffle = $this->getById($id);
        $raffle->update($data);
        return $raffle;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Raffle::destroy($id);
    }

}
