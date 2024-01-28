<?php

namespace App\Http\Controllers;

use App\Http\Filters\RequestFilter;
use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Validation\Rule;


class CampaignController extends Controller
{
    private CampaignRepositoryInterface $CampaignRepository;
    private UserRepositoryInterface $UserRepository;

    public function __construct(CampaignRepositoryInterface $CampaignRepository,
                                UserRepositoryInterface $UserRepository
    )
    {
        $this->CampaignRepository = $CampaignRepository;
        $this->UserRepository = $UserRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->CampaignRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $campaign = $this->CampaignRepository->getById($id);

            return response()->json([$campaign]);
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
            $campaign = $this->CampaignRepository->getByUuid($uuid);

            return response()->json([$campaign]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $campaign = $this->CampaignRepository->getBySlug($slug);

            return response()->json([$campaign]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByUserUuid(string $uuid, RequestFilter $filter, ?string $phone = null): Paginator
    {
        try {

            $page = request()->get('page') ?: 1;
            $limit = request()->get('limit') ?: 100;

            $start_date = request()->get('start_date') ?: null;
            $end_date = request()->get('end_date') ?: null;

            // Acesse o parâmetro keywords[tickets_number] usando o método input
            $tickets_req = request()->input('keywords.tickets_number', null);

            return $this->UserRepository->getCampaignByUserUuid($uuid, $limit, $page, $filter, $phone, $tickets_req, $start_date, $end_date);
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
                'description'     => 'required|string',
                'image'      => ['b64image', 'nullable'],
                'amount_tickets' => 'required|int',
                'support_number'    => 'required|string|max:255',
                'price_each_ticket'    => 'required|string|max:255',
                'min_ticket'    => 'required|int|max:10000',
                'max_ticket'    => 'required|int|max:10000',
                'show_date_of_raffle'    => 'required|boolean',
                'show_email_input'    => 'boolean|nullable',
                'show_top_ranking'    => 'boolean|nullable',
                'show_filters'    => 'boolean|nullable',
                'order_numbers'    => 'boolean|nullable',
                'ranking_acumulative'    => 'boolean|nullable',
                'visible'    => 'boolean|nullable',
                'dark_mode'    => 'boolean|nullable',
                'date_of_raffle'    => 'date_format:Y-m-d H:i:s|nullable',
                'time_wait_payment'    => 'required|string',
                'allow_terms'    => 'required|boolean',
                'url' => 'required|string|max:255',
                'category_id'  => 'required|int|exists:categories,id',
                'ticket_filter_id'  => 'required|int|exists:ticket_filters,id',
                'raffle_id'  => 'required|int|exists:raffles,id',
                'fee_id'  => 'required|int|exists:fees,id',
                'user_id'  => 'required|int|exists:users,id'
            ]);

            $campaign = $this->CampaignRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Campaign successfully'),
                'campaign' => $campaign
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

            $rules = [
                'name'        => 'string|max:125',
                'description'     => 'string',
                //'image'      => ['b64image', 'nullable'],
                'amount_tickets' => 'int',
                'support_number'    => 'string|max:255',
                //'status'    => 'int',
                'released_until_fee' => 'boolean|nullable',
                'price_each_ticket'    => 'string|max:255',
                'min_ticket'    => 'int|max:10000',
                'max_ticket'    => 'int|max:10000',
                'show_date_of_raffle'    => 'boolean',
                'show_email_input'    => 'boolean|nullable',
                'show_filters'    => 'boolean|nullable',
                'order_numbers'    => 'boolean|nullable',
                'show_top_ranking'    => 'boolean|nullable',
                'ranking_acumulative'    => 'boolean|nullable',
                'visible'    => 'boolean|nullable',
                'dark_mode'    => 'boolean|nullable',
                'date_of_raffle'    => 'date_format:Y-m-d H:i:s|nullable',
                'winner_collaborator_id'    => 'int|exists:collaborators,id|nullable',
                'closed'    => 'boolean|nullable',
                'sorted_number'    => 'string|max:100|nullable',
                'time_wait_payment'    => 'string',
                'allow_terms'    => 'boolean',
                'url' => 'string|max:255',
                'category_id'  => 'int|exists:categories,id',
                'ticket_filter_id'  => 'int|exists:ticket_filters,id',
                'raffle_id'  => 'int|exists:raffles,id',
                'fee_id'  => 'int|exists:fees,id',
                'user_id'  => 'int|exists:users,id'
            ];

            if (filter_var($request->input('image'), FILTER_VALIDATE_URL)) {
                // Remova 'image' das regras de validação
                unset($rules['image']);
            } else {
                // Adicione a regra 'b64image' para validar a imagem codificada em base64
                $rules['image'] = ['b64image', 'nullable'];
            }

            $validatedData = $request->validate($rules);

            $campaign = $this->CampaignRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Campaign successfully'),
                'campaign' => $campaign
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        try {

            $rules = [
                'status'    => 'int',
            ];

            $validatedData = $request->validate($rules);

            $campaign = $this->CampaignRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Campaign successfully'),
                'campaign' => $campaign
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
            if ($this->CampaignRepository->getById($id)) {
                $this->CampaignRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted campaign successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function deleteCampaignFile(int $id): JsonResponse
    {
        try {
            $campaign = $this->CampaignRepository->getById($id);

            if (@$campaign) {
                $this->CampaignRepository->deleteRandomNumbersFile($campaign);
            }

            return response()->json([
                'message' => __('Arquivo da Campanha Deletado')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}
