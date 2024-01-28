<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\AwardRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    private AwardRepositoryInterface $AwardRepository;

    public function __construct(AwardRepositoryInterface $AwardRepository)
    {
        $this->AwardRepository = $AwardRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->AwardRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $award = $this->AwardRepository->getById($id);

            return response()->json([$award]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByUserUuid(string $uuid, RequestFilter $filter): Paginator
    {
        try {

            $page = request()->get('page') ?: 1;
            $limit = request()->get('limit') ?: 100;

            return $this->AwardRepository->getByUserUuid($uuid, $limit, $page, $filter);

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
                'name'        => 'required|string|max:125',
                'user_id' => 'required|int|exists:users,id'
            ]);

            $award = $this->AwardRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Award successfully'),
                'award' => $award
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
                'name'        => 'string|max:125',
                'user_id' => 'int|exists:users,id'
            ]);

            $award = $this->AwardRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Award successfully'),
                'award' => $award
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
            if ($this->AwardRepository->getById($id)) {
                $this->AwardRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Award successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
