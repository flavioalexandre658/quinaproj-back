<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    private SaleRepositoryInterface $SaleRepository;

    public function __construct(SaleRepositoryInterface $SaleRepository)
    {
        $this->SaleRepository = $SaleRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->SaleRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $sale = $this->SaleRepository->getById($id);

            return response()->json([$sale]);
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

            return $this->SaleRepository->getByUserUuid($uuid, $limit, $page, $filter);

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
                'amount_tickets'        => 'required|int|max:100000',
                'amount_tickets_end'        => 'required|int|max:100000',
                'price_amount'        => 'required|string|max:10000',
                'user_id' => 'required|int|exists:users,id'
            ]);

            $sale = $this->SaleRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Sale successfully'),
                'sale' => $sale
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
                'amount_tickets'        => 'int|max:100000',
                'amount_tickets_end'        => 'int|max:100000',
                'price_amount'        => 'string|max:100000',
                'user_id' => 'int|exists:users,id'
            ]);

            $sale = $this->SaleRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Sale successfully'),
                'sale' => $sale
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
            if ($this->SaleRepository->getById($id)) {
                $this->SaleRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Sale successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
