<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\FeeRepositoryInterface;
use App\Models\Fee;

class FeeRepository implements FeeRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $fee = Fee::filter($filter);
        return $fee->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Fee
     * @throws \Exception
     */
    public function getById(int $id): Fee
    {
        $fee = Fee::find($id);
        if (!$fee) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $fee;
    }

    /**
     * @param array $data
     * @return Fee
     */
    public function create(array $data): Fee
    {
        return Fee::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Fee
     * @throws \Exception
     */
    public function update(int $id, array $data): Fee
    {
        $fee = $this->getById($id);
        $fee->update($data);
        return $fee;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Fee::destroy($id);
    }

}
