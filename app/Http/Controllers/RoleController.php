<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleRepositoryInterface $RoleRepository;

    public function __construct(RoleRepositoryInterface $RoleRepository)
    {
        $this->RoleRepository = $RoleRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->RoleRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $Role = $this->RoleRepository->getById($id);

            return response()->json([$Role]);
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
                'name'        => 'required|string|max:125'
            ]);

            $Role = $this->RoleRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Role successfully'),
                'role' => $Role
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
                'name'        => 'string|max:125'
            ]);

            $Role = $this->RoleRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Role successfully'),
                'role' => $Role
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
            if ($this->RoleRepository->getById($id)) {
                $this->RoleRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Role successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
