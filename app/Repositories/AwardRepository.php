<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\AwardRepositoryInterface;
use App\Models\Award;
use App\Models\User;
class AwardRepository implements AwardRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $award = Award::filter($filter);
        return $award->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Award
     * @throws \Exception
     */
    public function getById(int $id): Award
    {
        $award = Award::find($id);
        if (!$award) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $award;
    }

    public function getByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $user = User::where('uuid', $uuid)->first();
        $awards = Award::with('user')->where('user_id', $user['id'])->filter($filter);

        return $awards->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param array $data
     * @return Award
     */
    public function create(array $data): Award
    {
        return Award::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Award
     * @throws \Exception
     */
    public function update(int $id, array $data): Award
    {
        $award = $this->getById($id);
        $award->update($data);
        return $award;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Award::destroy($id);
    }

}
