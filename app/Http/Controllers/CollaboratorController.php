<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\CollaboratorRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollaboratorController extends Controller
{
    private CollaboratorRepositoryInterface $CollaboratorRepository;

    public function __construct(CollaboratorRepositoryInterface $CollaboratorRepository)
    {
        $this->CollaboratorRepository = $CollaboratorRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->CollaboratorRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $collaborator = $this->CollaboratorRepository->getById($id);

            return response()->json([$collaborator]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByUuid(string $uuid): JsonResponse
    {
        try {
            $collaborator = $this->CollaboratorRepository->getByUuid($uuid);

            return response()->json([$collaborator]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByIntervalDate(int $campaign_id): JsonResponse
    {
        try {
            $start_date = request()->get('start_date') ?: false;
            $end_date = request()->get('end_date') ?: false;
            $collaborators = $this->CollaboratorRepository->getCollaboratorsByIntervalDate($campaign_id, $start_date, $end_date);

            return response()->json([$collaborators]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByCampaignId(int $campaign_id, RequestFilter $filter, ?string $phone = null): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;


        $start_date = request()->get('start_date') ?: null;
        $end_date = request()->get('end_date') ?: null;

        // Acesse o parâmetro keywords[tickets_number] usando o método input
        $tickets_req = request()->input('keywords.tickets_number', null);

        return $this->CollaboratorRepository->getCollaboratorsByCampaignId($campaign_id, $limit, $page, $filter, $phone, $tickets_req, $start_date, $end_date);
    }

    public function getTicketsCollaborators(int $campaign_id, int $status): JsonResponse
    {
        try {
        $tickets = $this->CollaboratorRepository->getTicketsCollaborators($campaign_id, $status);
        return response()->json([$tickets]);
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
                'name' => 'required|string|max:255',
                'price_each_ticket' => 'string|max:125|nullable',
                'phone' => 'required|string|max:125',
                'email' => 'string|max:255|nullable',
                'url_checkout' => 'string|max:255|nullable',
                'status_payment' => 'int|nullable',
                'expire_date' => 'date_format:Y-m-d H:i:s|nullable',
                'amount_of_tickets'  => 'required|int',
                'numbers' => 'string|nullable',
                'campaign_id' => 'required|int|exists:campaigns,id',
                'allow_terms' => 'required|boolean'
            ]);

            $collaborator = $this->CollaboratorRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Collaborator successfully'),
                'collaborator' => $collaborator
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
                'name' => 'string|max:255',
                'price_each_ticket' => 'string|max:125|nullable',
                'phone' => 'string|max:125',
                'email' => 'string|max:255|nullable',
                'url_checkout' => 'string|max:255|nullable',
                'status_payment' => 'int|nullable',
                'expire_date' => 'date_format:Y-m-d H:i:s|nullable',
                'amount_of_tickets'  => 'int',
                'numbers' => 'string|nullable',
                'campaign_id' => 'int|exists:campaigns,id',
                'allow_terms' => 'required|boolean'
            ]);

            $collaborator = $this->CollaboratorRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Collaborator successfully'),
                'collaborator' => $collaborator
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
            if ($this->CollaboratorRepository->getById($id)) {
                $this->CollaboratorRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Collaborator successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
