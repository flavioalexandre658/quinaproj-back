<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $category = Category::filter($filter);
        return $category->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Category
     * @throws \Exception
     */
    public function getById(int $id): Category
    {
        $category = Category::find($id);
        if (!$category) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $category;
    }

    /**
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Category
     * @throws \Exception
     */
    public function update(int $id, array $data): Category
    {
        $category = $this->getById($id);
        $category->update($data);
        return $category;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Category::destroy($id);
    }
}
