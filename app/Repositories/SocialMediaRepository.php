<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\SocialMediaRepositoryInterface;
use App\Models\SocialMedia;

class SocialMediaRepository implements SocialMediaRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $socialMedia = SocialMedia::filter($filter);
        return $socialMedia->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return SocialMedia
     * @throws \Exception
     */
    public function getById(int $id): SocialMedia
    {
        $socialMedia = SocialMedia::find($id);
        if (!$socialMedia) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $socialMedia;
    }

    /**
     * @param array $data
     * @return SocialMedia
     */
    public function create(array $data): SocialMedia
    {
        return SocialMedia::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return SocialMedia
     * @throws \Exception
     */
    public function update(int $id, array $data): SocialMedia
    {
        $socialMedia = $this->getById($id);
        $socialMedia->update($data);
        return $socialMedia;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return SocialMedia::destroy($id);
    }

}
