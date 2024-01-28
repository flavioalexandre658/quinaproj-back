<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\User;

class RoleRepository implements RoleRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $role = Role::filter($filter);
        return $role->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Role
     * @throws \Exception
     */
    public function getById(int $id): Role
    {
        $role = Role::find($id);
        if (!$role) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $role;
    }

    /**
     * @param array $data
     * @return Role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Role
     * @throws \Exception
     */
    public function update(int $id, array $data): Role
    {
        $role = $this->getById($id);
        $role->update($data);
        return $role;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Role::destroy($id);
    }

}
