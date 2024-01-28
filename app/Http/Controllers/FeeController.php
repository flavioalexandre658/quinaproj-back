<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\FeeRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    private FeeRepositoryInterface $FeeRepository;

    public function __construct(FeeRepositoryInterface $FeeRepository)
    {
        $this->FeeRepository = $FeeRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->FeeRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $fee = $this->FeeRepository->getById($id);

            return response()->json([$fee]);
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
                'revenue'        => 'required|string|max:125',
                'min_revenue'        => 'required|numeric',
                'max_revenue'        => 'required|numeric|nullable',
                'fee'        => 'required|string|max:125'
            ]);

            $fee = $this->FeeRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Fee successfully'),
                'fee' => $fee
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
                'revenue'        => 'string|max:125',
                'min_revenue'        => 'numeric',
                'max_revenue'        => 'numeric|nullable',
                'fee'        => 'string|max:125'
            ]);

            $fee = $this->FeeRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Fee successfully'),
                'fee' => $fee
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
            if ($this->FeeRepository->getById($id)) {
                $this->FeeRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Fee successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
