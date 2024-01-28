<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\TicketFilterRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketFilterController extends Controller
{
    private TicketFilterRepositoryInterface $TicketFilterRepository;

    public function __construct(TicketFilterRepositoryInterface $TicketFilterRepository)
    {
        $this->TicketFilterRepository = $TicketFilterRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->TicketFilterRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $ticketFilter = $this->TicketFilterRepository->getById($id);

            return response()->json([$ticketFilter]);
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
                'description'        => 'string|max:255'
            ]);

            $ticketFilter = $this->TicketFilterRepository->create($validatedData);

            return response()->json([
                'message' => __('Created ticketFilter successfully'),
                'ticketFilter' => $ticketFilter
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
                'description'        => 'string|max:255'
            ]);

            $ticketFilter = $this->TicketFilterRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated ticketFilter successfully'),
                'ticketFilter' => $ticketFilter
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
            if ($this->TicketFilterRepository->getById($id)) {
                $this->TicketFilterRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted ticketFilter successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
