<?php

namespace App\Repositories;

use App\Helpers\ImageHelper;
use App\Http\Filters\Filter;
use App\Http\Filters\RequestFilter;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Collaborator;
use App\Models\Customization;
use App\Models\Fee;
use App\Models\PaymentMethod;
use App\Models\Raffle;
use App\Models\SocialMedia;
use App\Models\TicketFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ParagonIE\Sodium\Core\Curve25519\Fe;

define('USER_PATH', 'public/user-files/');

class UserRepository implements UserRepositoryInterface
{

    private ImageHelper $imageHelper;

    public function __construct(
        ImageHelper $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter, ?string $start_date = null, ?string $end_date = null): Paginator
    {
        $users = User::withCount([
            'campaigns',
            'campaigns as published_campaigns' => function ($query) {
                $query->where('status', 1);
            },
            'campaigns as released_campaigns' => function ($query) {
                $query->where('released_until_fee', 1);
            },
        ])
            ->with(['payments', 'paymentMethods'])
            ->where('id', '!=', 0)
            ->orderBy('created_at', 'desc');

        if ($start_date !== null && $end_date !== null) {
            $startDate = date($start_date); // Data de início do intervalo
            $endDate = date($end_date); // Data de fim do intervalo

            $users->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)->orderBy('created_at', 'desc');
            });
        }

        // Adicione informações extras antes de retornar
        $users->each(function ($user) {
            $user->total_campaigns = $user->campaigns_count;
            $user->published_campaigns = $user->published_campaigns;
            $user->released_campaigns = $user->released_campaigns;

            // Informações sobre payments (se existirem)
            if ($user->payment) {
                // Status do payment
                $user->payment_status = $user->payment->status; // Substitua 'status' pelo nome do campo real

                // Valor do payment
                $user->payment_value = $user->payment->value; // Substitua 'value' pelo nome do campo real
            }

            // Payment methods cadastrados com seus respectivos names
            $user->payment_methods = $user->paymentMethods->pluck('name_method')->toArray(); // Obtenha um array dos names_method

            // Você pode adicionar mais informações conforme necessário
            unset($user->campaigns); // Remova as campanhas se não forem necessárias na saída
            unset($user->payment);
            unset($user->paymentMethods);
        });

        // Aplica o filtro adicional, se fornecido
        if ($filter) {
            $users->filter($filter);
        }

        $users = $users->paginate($limit, ['*'], 'page', $page);

        return $users;
    }






    /**
     * @param int $id
     * @return User
     * @throws \Exception
     */
    public function getById(int $id): User
    {
        $user = User::with('paymentMethods', 'customizations', 'socialMedias', 'campaigns', 'campaigns.fee', 'campaigns.collaborators')->find($id);
        if (!$user) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $user;
    }

    public function getByUuid(string $uuid): User {
        $user = User::with(['awards', 'sales', 'paymentMethods', 'customizations', 'socialMedias', 'campaigns', 'campaigns.fee', 'campaigns.fee', 'campaigns.saleCampaigns.sale',
            'campaigns.awardCampaigns.award'])
            ->where('uuid', $uuid)
            ->first();

        $categories = Category::all();
        $filters = TicketFilter::all();
        $fees = Fee::all();
        $raffles = Raffle::all();

        $user->categories = $categories;
        $user->filters = $filters;
        $user->fees = $fees;
        $user->raffles = $raffles;

        if (!$user) {
            throw new \Exception(__('Não encontrado.'), 404);
        }

        foreach ($user->campaigns as $campaign) {
            $colaboradoresPagantes = Collaborator::where('status_payment', 1)
                ->where('campaign_id', $campaign->id)
                ->get();

            $quantidadeColaboradores = $colaboradoresPagantes->count();

            $faturamentoTotal = $colaboradoresPagantes->sum(function ($colaborador) {
                $price = str_replace(['R$', ','], ['', '.'], $colaborador->price_each_ticket);
                return $colaborador->amount_of_tickets * floatval($price);
            });

            $faturamentoTotalFormatado = 'R$' . number_format($faturamentoTotal, 2, ',', '.');

            $ticketMedio = $quantidadeColaboradores > 0 ? $faturamentoTotal / $quantidadeColaboradores : 0;
            $ticketMedioFormatado = 'R$' . number_format($ticketMedio, 2, ',', '.');

            $campaign->collaborators_paid = $quantidadeColaboradores;
            $campaign->revenue = $faturamentoTotalFormatado;
            $campaign->average_ticket = $ticketMedioFormatado;

            // Consulta para contar colaboradores por data para esta campanha
            $collaboratorsCountByDate = DB::table('collaborators')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('campaign_id', $campaign->id)
                ->whereDate('created_at', '<=', now())
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Transforma o resultado em um array associativo e adiciona à campanha
            $amount_collaborators_by_date = [];
            foreach ($collaboratorsCountByDate as $record) {
                $amount_collaborators_by_date[$record->date] = $record->count;
            }
            $campaign->amount_collaborators_by_date = $amount_collaborators_by_date;
        }

        return $user;
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        if (isset($data['image'])) {
            $imagePath = $this->imageHelper->storagePutB64Image(USER_PATH, $data['image']);
            $data['image'] = asset(str_replace('public', 'storage', $imagePath));
        }
        $data['uuid'] = Str::uuid();
        return User::create($data);
    }
    public function activeAccount($token) : JsonResponse
    {
        // Encontre o usuário associado a este token
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Token de ativação inválido.'], 400);
        }

        // Ative a conta
        $user->active = true;
        $user->activation_token = null; // Remova o token, pois a conta está ativa
        $user->save();

        return response()->json(['message' => 'Sua conta foi ativada com sucesso.']);
    }

    public function resetPassword(string $uuid, array $data): User
    {
        $user = User::where('uuid', $uuid)->first();

        if ($user) {
            $user->fill($data);
            $user->save();
            return $user;
        } else {
            // Adicione um tratamento para o caso em que o usuário não é encontrado.
            // Pode lançar uma exceção, retornar uma mensagem de erro, etc.
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

    }

    /**
     * @param int $id
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function update(int $id, array $data): User
    {
        $user = $this->getById($id);

        if (array_key_exists('image', $data)) {
            if($data['image']) {

                if($user->image) {
                    // Use parse_url() para obter o caminho da URL
                    $path = parse_url($user->image, PHP_URL_PATH);

                    // Use pathinfo() para obter o nome do arquivo
                    $filename = pathinfo($path, PATHINFO_BASENAME);
                    $this->imageHelper->deleteStorageFile(USER_PATH . '/' .$filename);
                }

                $data['image'] = asset(str_replace('public', 'storage', $this->imageHelper->updateStorageFile(
                    USER_PATH,
                    $data,
                    $user->getAttributeValue('image') ? $user->getAttributeValue('image') : ""
                )));
            }
        }

        if(!$user['uuid']){
            $data['uuid'] = Str::uuid();
        }

        $user->update($data);
        return $user;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $user = User::find($id);
        if($user->image) {
            // Use parse_url() para obter o caminho da URL
            $path = parse_url($user->image, PHP_URL_PATH);

            // Use pathinfo() para obter o nome do arquivo
            $filename = pathinfo($path, PATHINFO_BASENAME);
            $this->imageHelper->deleteStorageFile(USER_PATH . '/' .$filename);
        }
        return $user->delete();
    }

    public function getByEmail(string $email): User
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception(__('Item not found'), 404);
        }
        return $user;
    }


    public function getCampaignByUserUuid(string $uuid, int $limit, int $page, RequestFilter $filter, ?string $phone = null, ?string $tickets_req = null, ?string $start_date = null, ?string $end_date = null): Paginator
    {
        $user = User::where('uuid', $uuid)->first();

        $campaigns = Campaign::with('awardCampaigns', 'saleCampaigns')->where('user_id', $user['id'])
            ->orderBy('created_at');
            //->limit(5)
            //->get();

        if($start_date !== null && $end_date !== null){

            $startDate = date($start_date); // Data de início do intervalo
            $endDate = date($end_date); // Data de fim do intervalo

            $campaigns = Campaign::where('user_id', $user['id'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                });
        }

        // Adicione a condição para filtrar pelo campo phone se o parâmetro $phone for fornecido
        if ($phone !== null) {
            $campaigns->where('support_number', 'LIKE', '%' . $phone . '%');
        }

        // Verifique se o filtro é igual a 'tickets_number'
        if ($tickets_req) {
            $campaigns->where('status_payment', 1);
        }

        // Aplica o filtro adicional, se fornecido
        if ($filter) {
            $campaigns->filter($filter);
        }

        return $campaigns->paginate($limit, ['*'], 'page', $page);
    }

    public function getCustomizationByUserUuid(string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->first();
        $customizations = Customization::with('user')->where('user_id', $user['id'])
            ->get();

        if (!$customizations) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($customizations);
    }

    public function getSocialMediaByUserUuid(string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->first();
        $socialMedias = SocialMedia::with('user')->where('user_id', $user['id'])
            ->get();

        if (!$socialMedias) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($socialMedias);
    }

    public function getPaymentMethodByUserUuid(string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->first();
        $paymentMethods = PaymentMethod::where('user_id', $user['id'])
            ->get();

        if (!$paymentMethods) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($paymentMethods);
    }

    public function getPaymentMethodByUserId(int $id): JsonResponse
    {
        $user = User::where('id', $id)->first();
        $paymentMethods = PaymentMethod::where('user_id', $user['id'])
            ->get();

        if (!$paymentMethods) {
            throw new \Exception(__('Nenhum dado encontrado'), 404);
        }

        return response()->json($paymentMethods);
    }
}
