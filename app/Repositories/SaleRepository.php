<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\SaleRepositoryInterface;
use App\Models\Sale;

class SaleRepository implements SaleRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $sale = Sale::filter($filter);
        return $sale->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Sale
     * @throws \Exception
     */
    public function getById(int $id): Sale
    {
        $sale = Sale::find($id);
        if (!$sale) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $sale;
    }

    public function getByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $user = User::where('uuid', $uuid)->first();
        $sales = Sale::with('user')->where('user_id', $user['id'])->filter($filter);

        return $sales->paginate($limit, ['*'], 'page', $page);
    }
    /**
     * @param array $data
     * @return Sale
     */
    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Sale
     * @throws \Exception
     */
    public function update(int $id, array $data): Sale
    {
        $sale = $this->getById($id);
        $sale->update($data);
        return $sale;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Sale::destroy($id);
    }

}
