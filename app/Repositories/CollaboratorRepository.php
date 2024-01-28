<?php

namespace App\Repositories;

use App\Http\Controllers\MercadoPagoController;
use App\Http\Filters\RequestFilter;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Campaign;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SaleCampaign;
use App\Models\Ticket;
use App\Models\TicketFilter;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Models\Collaborator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nette\Utils\DateTime;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class CollaboratorRepository implements CollaboratorRepositoryInterface
{

    private MercadoPagoController $mercadoPagoController;

    public function __construct(MercadoPagoController $mercadoPagoController)
    {
        $this->mercadoPagoController = $mercadoPagoController;
    }
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $collaborator = Collaborator::filter($filter);
        return $collaborator->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Collaborator
     * @throws \Exception
     */
    public function getById(int $id): Collaborator
    {
        $collaborator = Collaborator::with('campaign','campaign.user', 'campaign.user.paymentMethods', 'campaign.user.customizations',  'campaign.user.socialMedias')->find($id);
        if (!$collaborator) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $collaborator;
    }
    public function getTicketsCollaborators(int $campaign_id, int $status): JsonResponse
    {
        $collaborators = Collaborator::where('campaign_id', $campaign_id)
            ->where('status_payment', $status)
            ->get();

        $campaign = Campaign::where('id', $campaign_id)
            ->with('user')
            ->first();

        $tickets = collect(); // Inicializa uma nova coleção vazia

        if($status != -1) {
            $ticketsWithStatus = [];

            if ($collaborators->isNotEmpty()) {
                foreach ($collaborators as $collaborator) {
                    $allNumbers = explode(',', $collaborator->numbers);

                    $status_tickets = '1';

                    if ($collaborator->status_payment == 1) {
                        $status_tickets = '-1';
                    } else if ($collaborator->status_payment == -1) {
                        $status_tickets = '1';
                    } else if ($collaborator->status_payment == 0) {
                        $status_tickets = '0';
                    }

                    foreach ($allNumbers as $index => $number) {
                        if ($number) {
                            $ticketsWithStatus[] = ['id' => $index + 1, 'number' => $number, 'status' => $status_tickets];
                        }
                    }
                }
            }

            $tickets = collect($ticketsWithStatus);
        }else{

            //PEGA NUMEROS
            if($campaign->ticket_filter_id == 1) {
                $userFolder = storage_path('public/campaigns-tickets/' . $campaign->user->uuid);
                $filePath = $userFolder . '/' . $campaign->uuid . '.txt';
                if (File::exists($filePath)) {
                    $content = File::get($filePath);
                    $allNumbers = explode(',', $content);

                    $collaboratorsPending = Collaborator::where('campaign_id', $campaign->id)
                        ->where('status_payment', 0)
                        ->whereNotNull('numbers')
                        ->get();

                    foreach ($collaboratorsPending as $collaboratorPending) {
                        $numbersToRemove = explode(',', $collaboratorPending->numbers);
                        foreach ($numbersToRemove as $number) {
                            // Remover o número da lista $allNumbers
                            $key = array_search($number, $allNumbers);
                            if ($key !== false) {
                                unset($allNumbers[$key]);
                            }
                        }
                    }

                    $numbersWithStatus = [];
                    foreach ($allNumbers as $index => $number) {
                        if($number) {
                            $numbersWithStatus[] = ['id' => $index + 1, 'number' => $number, 'status' => '1'];
                        }
                    }

                    $tickets = collect($numbersWithStatus);
                }

            }
        }

        return response()->json($tickets);
    }

    public function getByUuid(string $uuid): Collaborator
    {
        $collaborator = Collaborator::with('payments', 'campaign', 'campaign.raffle',  'campaign.user', 'campaign.user.paymentMethods', 'campaign.user.customizations',  'campaign.user.socialMedias')
            ->where('uuid', $uuid)->first();
        if (!$collaborator) {
            throw new \Exception(__('Não encontrado.'), 404);
        }

        $numbers = explode(',', $collaborator->numbers);

        $formattedNumbers = array_map(function($number) {
            return '[' . $number . ']';
        }, $numbers);

        $collaborator->numbers = implode(' ', $formattedNumbers);

        return $collaborator;
    }

    /**
     * @param array $data
     * @return Collaborator
     */
    public function create(array $data): Collaborator
    {
        $campaign = Campaign::where('id', $data['campaign_id'])->with(['user', 'user.paymentMethods'])->first();
        $day_of_changes = '2024-01-13 00:00:01';
        $amountOfTickets = $data['amount_of_tickets'];
        //$data['ticket_ids'] = json_decode($data['ticket_ids']);
        $real_available = ($campaign->amount_tickets - ($campaign->unavailable_tickets + $campaign->pending_tickets));
        $hasInserted = false;

        if($amountOfTickets > $real_available){
            throw new \Exception(__('Só restam '.$real_available.' bilhetes disponíveis.'), 404);
        }

        if(!$data['allow_terms']) {
            throw new \Exception(__('Aceite os termos para prosseguir'), 404);
        }

        $payMethods = PaymentMethod::where('user_id', $campaign->user_id)->get();

        $payMethod = null;

        if(@$payMethods) {
            foreach ($payMethods as $paymentMethod) {
                if ($paymentMethod["name_method"] === "Mercado Pago" && $paymentMethod["status"] === 1) {
                    if(!$data['email']) {
                        throw new \Exception(__('Você deve inserir um email.'), 404);
                    }else{
                        $payMethod = 'Mercado Pago';
                    }
                    break; // Se encontrar, podemos interromper o loop
                }else if($paymentMethod["name_method"] === "Transferência PIX" && $paymentMethod["status"] === 1){
                    $payMethod = 'PIX';
                    break;
                }
            }
        }


        if ($amountOfTickets >= $campaign->min_ticket && $amountOfTickets <= $campaign->max_ticket) {

            // Calcular a data de expiração com base no time_wait_payment
            $expireDate = Carbon::now('America/Sao_Paulo');
            $timeWaitPayment = $campaign['time_wait_payment'];

            if (str_contains($timeWaitPayment, 'm') !== false) {
                $minutes = (int)substr($timeWaitPayment, 0, -1);
                $expireDate->addMinutes($minutes);
            } elseif (str_contains($timeWaitPayment, 'd') !== false) {
                $days = (int)substr($timeWaitPayment, 0, -1);
                $expireDate->addDays($days);
            } elseif (str_contains($timeWaitPayment, 'h') !== false) {
                $hours = intval(substr($timeWaitPayment, 0, -1));
                $expireDate->addHours($hours);
            }

            $salesCampaign = SaleCampaign::with(['sale'])->where('campaign_id', $data['campaign_id'])->get()->toArray();

            if($salesCampaign) {
                foreach ($salesCampaign as $element) {
                    if($element['sale']['amount_tickets'] <= $data['amount_of_tickets'] && $data['amount_of_tickets']  <= $element['sale']['amount_tickets_end']){
                        $campaign['price_each_ticket'] = $element['sale']['price_amount'];
                    }

                    if($element['sale']['amount_tickets_end'] == $element['sale']['amount_tickets'] && $data['amount_of_tickets'] == $element['sale']['amount_tickets']){
                        $campaign['price_each_ticket'] = $element['sale']['price_amount'];
                    }

                    if(!$element['sale']['amount_tickets_end'] && $data['amount_of_tickets'] == $element['sale']['amount_tickets']){
                        $campaign['price_each_ticket'] = $element['sale']['price_amount'];
                    }
                }
            }

            $expireDate = $expireDate->format('Y-m-d H:i:s');
            // Criar o colaborador e definir os campos relevantes
            $collaborator = new Collaborator();
            $collaborator['uuid'] = Str::uuid();
            $collaborator['name'] = $data['name'];
            $collaborator['phone'] = $data['phone'];
            $collaborator['email'] = $data['email'];
            $collaborator['amount_of_tickets'] = $data['amount_of_tickets'];
            $collaborator['campaign_id'] = $data['campaign_id'];
            $collaborator['expire_date'] = $expireDate;
            $collaborator['price_each_ticket'] = $campaign->price_each_ticket;
            $collaborator['status_payment'] = 0;

            if(@$data['numbers']) {
                $number_selected = null;
                $numbers = explode(',', $data['numbers']);
                foreach ($numbers as $value) {
                    $collab = Collaborator::where('campaign_id', $collaborator->campaign_id)->whereRaw("FIND_IN_SET(?, numbers)", [$value])->first();
                    if($collab) {
                        if($collab->status_payment == 0 || $collab->status_payment == 1) {
                            $hasInserted = true;
                            $number_selected = $value;
                        }else if($collab->status_payment == -1){
                            $this->delete($collab->id);
                        }
                    }
                }

                if($hasInserted){
                    throw new \Exception(__('O número ['. $number_selected .'] já foi reservado.'), 404);
                }

                $collaborator['numbers'] = $data['numbers'];
            }
            $collaborator['allow_terms'] = $data['allow_terms'];


            // Salvar o colaborador no banco de dados
            $collaborator->save();

            if($payMethod == 'Mercado Pago'){
                try {
                    $request = Request::createFromBase(
                        Request::create(
                            '/',
                            'POST',
                            [
                                "campaign_id" => $campaign->id,
                                "collaborator_id" => $collaborator->id
                            ]
                        )
                    );

                    // Crie uma instância de MercadoPagoController
                    $mercadoPagoController = new MercadoPagoController();

                    // Chame o método de instância
                    $res = $mercadoPagoController->processPaymentPix($request);
                    $res = json_decode(json_encode($res), true);

                    if (!@$res['original']['payment']['error']) {

                        $price_ea_ticket = str_replace("R$", "", $collaborator['price_each_ticket']);
                        $price_ea_ticket = (float)str_replace(",", ".", $price_ea_ticket);
                        $amount = $price_ea_ticket * $collaborator['amount_of_tickets'];

                        $payment = Payment::create([
                            'transaction_id' => $res['original']['payment']['id'],
                            'amount' => ceil($amount * 100) / 100,
                            'currency' => 'R$',
                            'status' => $res['original']['payment']['status'],
                            "campaign_id" => $campaign->id,
                            "collaborator_id" => $collaborator->id
                        ]);

                        $collaborator->payment = $payment;
                    }else{
                        throw new \Exception(__('Criador da Ação precisa Revisar as Configurações do Mercado Pago'), 404);
                    }
                }catch (\Exception $e){
                    //Log::error('Exceção: ' . $e->getMessage(), ['exception' => $e]);
                    throw new \Exception(__($e->getMessage()), 404);
                }
            }else if($payMethod == 'PIX'){

                try {
                    $price_ea_ticket = str_replace("R$", "", $collaborator['price_each_ticket']);
                    $price_ea_ticket = (float)str_replace(",", ".", $price_ea_ticket);
                    $amount = $price_ea_ticket * $collaborator['amount_of_tickets'];

                    $payment = Payment::create([
                        'transaction_id' => Str::uuid(),
                        'amount' => ceil($amount * 100) / 100,
                        'currency' => 'R$',
                        'status' => 'pending',
                        "campaign_id" => $campaign->id,
                        "collaborator_id" => $collaborator->id
                    ]);

                    $collaborator->payment = $payment;
                }catch (\Exception $e){
                    throw new \Exception(__($e->getMessage()), 404);
                }

            }

            return $collaborator;

        } else {
            // A quantidade de tickets está fora do intervalo permitido
            throw new \Exception(__('Escolha no mínimo '.$campaign->min_ticket.' bilhete(s) ou no máximo '.$campaign->max_ticket.' bilhete(s).'), 404);
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return Collaborator
     * @throws \Exception
     */
    public function update(int $id, array $data): Collaborator
    {
        $collaborator = $this->getById($id);
        $collaborator->update($data);

        if (isset($data['status_payment'])) {
            $newPaymentStatus = 'pending';

            if ($data['status_payment'] == -1) {
                $newPaymentStatus = 'cancelled';
            } elseif ($data['status_payment'] == 1) {
                $newPaymentStatus = 'approved';
            }

            $payment = Payment::where('collaborator_id', $id)->first();

            if ($payment) {
                $payment->update(['status' => $newPaymentStatus]);
            } else {
                $collaborator = Collaborator::where('id', $id)->with('campaign')->first();

                $status_payment_collaborator = '0';

                if ($newPaymentStatus == 'approved') {
                    $status_payment_collaborator = '1';
                } else {
                    if ($collaborator->status_payment != '1') {
                        if ($newPaymentStatus != 'pending') {
                            $status_payment_collaborator = '-1';
                        }
                    } else {
                        $status_payment_collaborator = $collaborator->status_payment;
                    }
                }

                $collaborator->status_payment = $status_payment_collaborator;
                $collaborator->save();

                if ($status_payment_collaborator === '1') {
                    $this->reserveNumbers($collaborator);
                } elseif ($status_payment_collaborator === '-1') {
                    $this->cancelNumbers($collaborator);
                }
            }
        }

        return $collaborator;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $collaborator = Collaborator::where('id', $id)->with('campaign', 'campaign.user')->first();

        if ($collaborator->numbers) {
            $this->cancelNumbers($collaborator);
        }

        return Collaborator::destroy($id);
    }


    public function getCollaboratorsByCampaignId(int $campaign_id, int $limit, int $page, RequestFilter $filter, ?string $phone = null, ?string $tickets_req = null, ?string $start_date = null, ?string $end_date = null): Paginator
    {
        $collaborators = Collaborator::with('campaign', 'payments')
            ->where('campaign_id', $campaign_id);

        // Adicione a condição para filtrar pelo campo phone se o parâmetro $phone for fornecido
        if ($phone !== null) {
            $collaborators->where('phone', 'LIKE', '%' . $phone . '%');
        }

        if ($start_date !== null && $end_date !== null) {
            $startDate = date($start_date); // Data de início do intervalo
            $endDate = date($end_date); // Data de fim do intervalo

            $collaborators->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            });
        }

        // Verifique se o filtro é igual a 'tickets_number'
        if ($tickets_req) {
            $collaborators->where('status_payment', 1);
        }

        // Aplica o filtro adicional, se fornecido
        if ($filter) {
            $collaborators->filter($filter);
        }

        // Execute a query e obtenha os resultados
        $collaboratorsResults = $collaborators->paginate($limit, ['*'], 'page', $page);

        if ($collaboratorsResults->count()) {
            foreach ($collaboratorsResults->items() as $collaborator) {
                $allNumbers = explode(',', $collaborator->numbers);

                $status_tickets = '1';

                if ($collaborator->status_payment == 1) {
                    $status_tickets = '-1';
                } else if ($collaborator->status_payment == -1) {
                    $status_tickets = '1';
                } else if ($collaborator->status_payment == 0) {
                    $status_tickets = '0';
                }
                $numbersWithStatus = [];
                foreach ($allNumbers as $index => $number) {
                    if ($number) {
                        $numbersWithStatus[] = ['id' => $index + 1, 'number' => $number, 'status' => $status_tickets];
                    }
                }

                $collaborator->numbers = $numbersWithStatus;
            }
        }

        return $collaboratorsResults;
    }





    public function getCollaboratorsByIntervalDate(int $campaign_id, string $start_date, string $end_date): JsonResponse
    {
        $campaign = Campaign::find($campaign_id);
        if ($campaign === null) {
            throw new \Exception(__('Campanha não encontrada'), 404);
        }

        // Formatar $campaign['created_at'] para Y-m-d
        $created_at = $campaign['created_at']->format('Y-m-d');

        // Verificar se $start_date é menor que $campaign['created_at']
        if ($start_date < $created_at) {
            $start_date = $created_at;
        }


        $startDate = date($start_date); // Data de início do intervalo
        $endDate = date($end_date); // Data de fim do intervalo

        $collaborators = Collaborator::where('campaign_id', $campaign_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->get();

        if ($collaborators->isEmpty()) {
            $collaborators = $this->fillMissingDates($startDate, $endDate);
        }

        return response()->json($collaborators);
    }
    private function fillMissingDates($startDate, $endDate)
    {
        $dates = [];
        $currentDate = new DateTime($startDate);

        while ($currentDate <= $endDate) {
            $formattedDate = $currentDate->format('Y-m-d');
            $dates[$formattedDate] = 0;
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function reserveManually($collaborator, $filePath){

        $selectedNumbers = $collaborator->numbers;
        $numberOfNumbers = $collaborator->amount_of_tickets;

        if (File::exists($filePath)) {
            // Método para pegar os números específicos passados
            $selectedNumbersArray = explode(',', $selectedNumbers);

            $content = File::get($filePath);
            $allNumbers = explode(',', $content);

            // Verificar se há números suficientes
            if (count($allNumbers) >= $numberOfNumbers) {
                $retrievedNumbers = array_intersect($selectedNumbersArray, $allNumbers);

                if(sizeof($retrievedNumbers) == sizeof($selectedNumbersArray)) {

                    $remainingNumbers = array_diff($allNumbers, $retrievedNumbers);

                    try {
                        File::put($filePath, implode(',', $remainingNumbers), LOCK_EX);
                    }catch (\Exception $e){

                    }

                    $totalNumbersEnd = count($remainingNumbers);
                    $collaborator->campaign->available_tickets = $totalNumbersEnd;

                    if ($collaborator->campaign->pending_tickets) {
                        $totalAmountOfTickets = Collaborator::where('campaign_id', $collaborator->campaign_id)
                            ->where('status_payment', 0)
                            ->sum('amount_of_tickets');

                        $collaborator->campaign->pending_tickets = $totalAmountOfTickets;
                    }

                    $collaborator->campaign->unavailable_tickets = $collaborator->campaign->amount_tickets - $totalNumbersEnd;

                    $this->updateCampaignTickets($collaborator);
                    $this->updateCollaboratorNumbers($collaborator, $retrievedNumbers);

                }
            }


        }
    }

    private function reserveAutomatic($collaborator, $filePath){

        $numberOfNumbers = $collaborator->amount_of_tickets;

        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $numbers = explode(',', $content);

            // Verificar se há números suficientes
            if (count($numbers) >= $numberOfNumbers) {
                $retrievedNumbers = array_slice($numbers, 0, $numberOfNumbers);


                $remainingNumbers = array_slice($numbers, $numberOfNumbers);

                try {
                    File::put($filePath, implode(',', $remainingNumbers),LOCK_EX);
                }catch (\Exception $e){

                }


                $totalNumbersEnd = count($numbers) - $numberOfNumbers;
                $collaborator->campaign->available_tickets = $totalNumbersEnd;
                if ($collaborator->campaign->pending_tickets) {
                    $totalAmountOfTickets = Collaborator::where('campaign_id', $collaborator->campaign_id)
                        ->where('status_payment', 0)
                        ->sum('amount_of_tickets');

                    $collaborator->campaign->pending_tickets = $totalAmountOfTickets;
                }
                $collaborator->campaign->unavailable_tickets = $collaborator->campaign->amount_tickets - $totalNumbersEnd;

                $this->updateCampaignTickets($collaborator);
                $this->updateCollaboratorNumbers($collaborator, $retrievedNumbers);

            }
        }
    }
    public function reserveNumbers($collaborator) {
        $userFolder = storage_path('public/campaigns-tickets/' . $collaborator->campaign->user->uuid);
        $filePath = $userFolder . '/' . $collaborator->campaign->uuid . '.txt';

        if ($collaborator->campaign->ticket_filter_id == 1 && $collaborator->numbers !== null) {
            $this->reserveManually($collaborator, $filePath);
        } elseif ($collaborator->campaign->ticket_filter_id == 2) {
            $this->reserveAutomatic($collaborator, $filePath);
        }
    }

    public function re_reserveNumbers($collaborator) {
        $userFolder = storage_path('public/campaigns-tickets/' . $collaborator->campaign->user->uuid);
        $filePath = $userFolder . '/' . $collaborator->campaign->uuid . '.txt';

        $selectedNumbers = $collaborator->numbers;
        $numberOfNumbers = sizeof($collaborator->tickets);

        if (File::exists($filePath)) {
            // Limpar as variáveis para liberar memória
            unset($allNumbers, $remainingNumbers, $selectedNumbersArray);

            // Método para pegar os números específicos passados
            $selectedNumbersArray = explode(',', $selectedNumbers);

            $content = File::get($filePath);
            $allNumbers = explode(',', $content);

            // Verificar se há números suficientes
            if (count($allNumbers) >= $numberOfNumbers) {
                //$retrievedNumbers = array_intersect($selectedNumbersArray, $allNumbers);
                //$remainingNumbers = array_diff($allNumbers, $retrievedNumbers);
                $remainingNumbers = $this->flipIssetDiff($allNumbers, $selectedNumbersArray);

                try {
                    File::put($filePath, implode(',', $remainingNumbers), LOCK_EX);
                }catch (\Exception $e){

                }

                $this->updateCollaboratorNumbers($collaborator, $selectedNumbersArray);

                // Limpar as variáveis para liberar memória
                unset($allNumbers, $remainingNumbers, $selectedNumbersArray, $retrievedNumbers);

            }



        }
    }

    private function flipIssetDiff($b, $a) {
        $at = array_flip($a);
        $d = array();
        foreach ($b as $i)
            if (!isset($at[$i]))
                $d[] = $i;

        return $d;
    }

    private function updateCampaignTickets($collaborator){
        try {
            Campaign::where('id', $collaborator->campaign->id)->update(['available_tickets' => $collaborator->campaign->available_tickets, 'pending_tickets' => $collaborator->campaign->pending_tickets, 'unavailable_tickets' => $collaborator->campaign->unavailable_tickets]);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar a campanha: ' . $e->getMessage());
        }
    }

    private function updateCollaboratorNumbers($collaborator, $retrievedNumbers){
        try {
            Collaborator::where('id', $collaborator->id)->update(['numbers' => $retrievedNumbers ? implode(',', $retrievedNumbers) : null]);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar colaborador: ' . $e->getMessage());
        }
    }

    private function cancelManually($collaborator, $numbers, $filePath){

        $totalNumbers = count($numbers);
        $numbersToAdd = @$collaborator->numbers;
        $howMuchCanInsert = $collaborator->campaign->amount_tickets - $totalNumbers;

        // Adicionar os novos números
        $newNumbers = explode(',', $numbersToAdd);

        $intersect = array_intersect($newNumbers, $numbers);

        if(!sizeof($intersect)){

            if ($howMuchCanInsert >= $collaborator->amount_of_tickets) {


                $combinedNumbers = array_merge($numbers, $newNumbers);

                // Escreve de volta ao arquivo

                try {
                    File::put($filePath, implode(',', $combinedNumbers), LOCK_EX);
                }catch (\Exception $e){

                }

            }
        }

        $content = File::get($filePath);
        $numbersNow = explode(',', $content);

        $totalNumbersNow = count($numbersNow);

        $collaborator->campaign->available_tickets = $totalNumbersNow;
        $collaborator->campaign->unavailable_tickets = $collaborator->campaign->amount_tickets - $totalNumbersNow;
        if ($collaborator->campaign->pending_tickets) {
            $totalAmountOfTickets = Collaborator::where('campaign_id', $collaborator->campaign_id)
                ->where('status_payment', 0)
                ->sum('amount_of_tickets');

            $collaborator->campaign->pending_tickets = $totalAmountOfTickets;
        }
        $this->updateCampaignTickets($collaborator);
        $this->updateCollaboratorNumbers($collaborator, $newNumbers);
    }

    private function cancelAutomatic($collaborator, $numbers, $filePath){

        $totalNumbers = count($numbers);
        $numbersToAdd = @$collaborator->numbers;
        $howMuchCanInsert = $collaborator->campaign->amount_tickets - $totalNumbers;

        // Adicionar os novos números
        $newNumbers = explode(',', $numbersToAdd);

        if ($howMuchCanInsert >= $collaborator->amount_of_tickets) {


            $combinedNumbers = array_merge($numbers, $newNumbers);

            // Escreve de volta ao arquivo
            try {
                File::put($filePath, implode(',', $combinedNumbers), LOCK_EX);
            }catch (\Exception $e){

            }


            $content = File::get($filePath);
            $numbersNow = explode(',', $content);

            $totalNumbersNow = count($numbersNow);

            $collaborator->campaign->available_tickets = $totalNumbersNow;
            $collaborator->campaign->unavailable_tickets = $collaborator->campaign->amount_tickets - $totalNumbersNow;
            if ($collaborator->campaign->pending_tickets) {
                $totalAmountOfTickets = Collaborator::where('campaign_id', $collaborator->campaign_id)
                    ->where('status_payment', 0)
                    ->sum('amount_of_tickets');

                $collaborator->campaign->pending_tickets = $totalAmountOfTickets;
            }

            $this->updateCampaignTickets($collaborator);
            $this->updateCollaboratorNumbers($collaborator, null);

        }
    }
    public function cancelNumbers($collaborator) {
        $userFolder = storage_path('public/campaigns-tickets/' . $collaborator->campaign->user->uuid);
        $filePath = $userFolder . '/' . $collaborator->campaign->uuid . '.txt';

        if($collaborator->numbers) {
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $numbers = explode(',', $content);

                if($collaborator->campaign->ticket_filter_id == 1) {
                    $this->cancelManually($collaborator, $numbers, $filePath);
                }elseif($collaborator->campaign->ticket_filter_id == 2) {
                    $this->cancelAutomatic($collaborator, $numbers, $filePath);
                }
            }
        }else{
            if ($collaborator->campaign->pending_tickets) {
                $collaborator->campaign->pending_tickets = $collaborator->campaign->pending_tickets - $collaborator->amount_of_tickets;
                $this->updateCampaignTickets($collaborator);
            }
        }
    }

    private function reserveTickets($data, $collaborator)
    {
        try {
            $campaign = Campaign::find($data['campaign_id']);
            if ($campaign['available_tickets'] > 0 && $data['amount_of_tickets'] <= $campaign['available_tickets']) {
                try {

                    $ticketFilter = TicketFilter::find($campaign['ticket_filter_id']);
                    if(strpos($ticketFilter['name'], 'manualmente')){

                        $ticketIds = $data['ticket_ids'];

                        // Reserve os bilhetes manualmente, definindo o status e o ID do colaborador
                        Ticket::whereIn('id', $ticketIds)->update([
                            'status' => '0',
                            'collaborator_id' => $collaborator['id']
                        ]);

                    }else{

                        DB::beginTransaction();

                        try {
                            $totalTickets = $data['amount_of_tickets'];
                            $batchSize = 1000; // Tamanho do lote para atualização

                            $ticketsUpdated = 0;
                            while ($ticketsUpdated < $totalTickets) {
                                $remainingTickets = $totalTickets - $ticketsUpdated;
                                $currentBatchSize = min($batchSize, $remainingTickets);

                                $tickets = $campaign->tickets()
                                    ->where('status', '1')
                                    ->orderByRaw('RAND()')
                                    ->limit($currentBatchSize)
                                    ->get();

                                $ticketIds = $tickets->pluck('id')->toArray();

                                Ticket::whereIn('id', $ticketIds)->update([
                                    'status' => '0',
                                    'collaborator_id' => $collaborator['id']
                                ]);

                                $ticketsUpdated += $currentBatchSize;
                            }

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }


                    return true;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
