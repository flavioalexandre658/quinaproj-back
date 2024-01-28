<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private TicketRepositoryInterface $TicketRepository;

    public function __construct(TicketRepositoryInterface $TicketRepository)
    {
        $this->TicketRepository = $TicketRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->TicketRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $ticket = $this->TicketRepository->getById($id);

            return response()->json([$ticket]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * @param int $campaignId
     * @param int $ticketNumber
     * @return JsonResponse
     */
    public function getByCampaignIdAndTicketNumber(int $campaignId, int $ticketNumber): JsonResponse
    {
        try {
            $ticket = $this->TicketRepository->getByCampaignIdAndTicketNumber($campaignId, $ticketNumber);

            return response()->json([$ticket]);
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
                'collaborator_id'        => 'int|exists:collaborators,id|nullable',
                'campaign_id'        => 'required|int|exists:campaigns,id',
                'number'        => 'required|string'
            ]);

            $ticket = $this->TicketRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Ticket successfully'),
                'ticket' => $ticket
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
                'collaborator_id'        => 'int|exists:collaborators,id|nullable',
                'campaign_id'        => 'int|exists:campaigns,id',
                'number'        => 'string',
                'status' => 'int'
            ]);

            $ticket = $this->TicketRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Ticket successfully'),
                'ticket' => $ticket
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
            if ($this->TicketRepository->getById($id)) {
                $this->TicketRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Ticket successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByCampaignId(int $campaignId, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->TicketRepository->getByCampaignId($campaignId, $limit, $page, $filter);
    }

    public function getByCollaboratorId(int $collaboratorId, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->TicketRepository->getByCollaboratorId($collaboratorId, $limit, $page, $filter);
    }

    public function getByCollaboratorIdPaid(int $collaboratorId, RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->TicketRepository->getByCollaboratorIdPaid($collaboratorId, $limit, $page, $filter);
    }
}
