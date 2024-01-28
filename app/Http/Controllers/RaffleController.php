<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\RaffleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    private RaffleRepositoryInterface $RaffleRepository;

    public function __construct(RaffleRepositoryInterface $RaffleRepository)
    {
        $this->RaffleRepository = $RaffleRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->RaffleRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $raffle = $this->RaffleRepository->getById($id);

            return response()->json([$raffle]);
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
                'name'        => 'required|string|max:255',
                'url'        => 'required|string|max:255'
            ]);

            $raffle = $this->RaffleRepository->create($validatedData);

            return response()->json([
                'message' => __('Created raffle successfully'),
                'raffle' => $raffle
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
                'name'        => 'string|max:255',
                'url'        => 'string|max:255'
            ]);

            $raffle = $this->RaffleRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated raffle successfully'),
                'raffle' => $raffle
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
            if ($this->RaffleRepository->getById($id)) {
                $this->RaffleRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted raffle successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
