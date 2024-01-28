<?php

namespace App\Repositories;

use App\Helpers\ImageHelper;
use App\Http\Filters\RequestFilter;
use App\Models\Collaborator;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\CampaignRepositoryInterface;
use App\Models\Campaign;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use mysql_xdevapi\Exception;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

define('CAMPAIGN_PATH', 'public/campaign-files/');
define('CAMPAIGN_PATH_TICKETS', 'public/campaigns-tickets/');
class CampaignRepository implements CampaignRepositoryInterface
{

    private ImageHelper $imageHelper;
    private Ticket $ticket;

    public function __construct(
        ImageHelper $imageHelper,
        Ticket $ticket
    ) {
        $this->imageHelper = $imageHelper;
        $this->ticket = $ticket;
    }

    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $campaign = Campaign::with(['category', 'ticketFilter', 'raffle', 'fee', 'user'])->filter($filter);
        return $campaign->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Campaign
     * @throws \Exception
     */
    public function getById(int $id): Campaign
    {
        $campaign = Campaign::with([
            'category',
            'ticketFilter',
            'raffle',
            'fee',
            'user',
            'user.paymentMethods',
            'user.socialMedias',
            'user.customizations',
            'saleCampaigns.sale', // Carrega os sales relacionados à SaleCampaign
            'awardCampaigns.award', // Carrega os awards relacionados à AwardCampaign
            'winnerCollaborator'
        ])->find($id);

        if (!$campaign) {
            throw new \Exception(__('Item not found'), 404);
        }

        return $campaign;
    }

    public function getByUuid(string $uuid): Campaign {
        $campaign = Campaign::with([
            'category',
            'ticketFilter',
            'raffle',
            'payments',
            'fee',
            'user',
            'user.paymentMethods',
            'user.socialMedias',
            'user.customizations',
            'user.awards',
            'user.sales',
            'saleCampaigns.sale',
            'awardCampaigns.award',
            'winnerCollaborator'
        ])->where('uuid', $uuid)->first();

        if (!$campaign) {
            throw new \Exception(__('Item not found'), 404);
        }

        // Obter top 3 colaboradores
        $top3Collaborators = Collaborator::where('campaign_id', $campaign->id)
            ->where('status_payment', 1)
            ->orderByDesc('amount_of_tickets')
            ->take(3)
            ->get();

        // Calcular ranking acumulativo baseado no número de telefone
        $collaboratorsWithPhone = Collaborator::where('campaign_id', $campaign->id)
            ->where('status_payment', 1)
            ->get()
            ->groupBy('phone');

        $top3CollaboratorsAcumulative = $collaboratorsWithPhone->map(function ($collabs, $phone) {
            $totalTickets = $collabs->sum('amount_of_tickets');
            $representativeCollaborator = $collabs->sortByDesc('amount_of_tickets')->first();

            return [
                'collaborator' => $representativeCollaborator,
                'total_tickets' => $totalTickets
            ];
        })->sortByDesc('total_tickets')
            ->take(3)
            ->values()
            ->all();

        // Adicionar os resultados ao objeto campaign
        $campaign->top3_collaborators = $top3Collaborators;
        $campaign->top3_collaborators_acumulative = $top3CollaboratorsAcumulative;

        //PEGA NUMEROS
        if($campaign->ticket_filter_id == 1) {
            $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);
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

                $campaign->numbers = $numbersWithStatus;
            }

        }

        if($campaign->winner_collaborator_id){
            $numbers = explode(',', $campaign->winnerCollaborator->numbers);

            $formattedNumbers = array_map(function($number) {
                return '[' . $number . ']';
            }, $numbers);

            $campaign->winnerCollaborator->numbers = implode(' ', $formattedNumbers);
        }

        return $campaign;
    }

    public function getBySlug(string $slug): Campaign {
        $campaign = Campaign::with([
            'category',
            'ticketFilter',
            'raffle',
            'fee',
            'user',
            'user.paymentMethods',
            'user.socialMedias',
            'user.customizations',
            'user.awards',
            'user.sales',
            'saleCampaigns.sale',
            'awardCampaigns.award',
            'awardCampaigns.collaborator',
            'winnerCollaborator'
        ])->where('slug', $slug)->first();

        if (!$campaign) {
            throw new \Exception(__('Item not found'), 404);
        }

        // Obter top 3 colaboradores
        $top3Collaborators = Collaborator::where('campaign_id', $campaign->id)
            ->where('status_payment', 1)
            ->orderByDesc('amount_of_tickets')
            ->take(3)
            ->get();

        // Calcular ranking acumulativo baseado no número de telefone
        $collaboratorsWithPhone = Collaborator::where('campaign_id', $campaign->id)
            ->where('status_payment', 1)
            ->get()
            ->groupBy('phone');

        $top3CollaboratorsAcumulative = $collaboratorsWithPhone->map(function ($collabs, $phone) {
            $totalTickets = $collabs->sum('amount_of_tickets');
            $representativeCollaborator = $collabs->sortByDesc('amount_of_tickets')->first();

            return [
                'collaborator' => $representativeCollaborator,
                'total_tickets' => $totalTickets
            ];
        })->sortByDesc('total_tickets')
            ->take(3)
            ->values()
            ->all();

        // Adicionar os resultados ao objeto campaign
        $campaign->top3_collaborators = $top3Collaborators;
        $campaign->top3_collaborators_acumulative = $top3CollaboratorsAcumulative;


        //PEGA NUMEROS
        if($campaign->ticket_filter_id == 1) {
            $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);
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

                // Verificar se a ordenação é necessária
                if ($campaign->order_numbers) {
                    sort($allNumbers);
                }

                $numbersWithStatus = [];
                foreach ($allNumbers as $index => $number) {
                    if($number) {
                        $numbersWithStatus[] = ['id' => $index + 1, 'number' => $number, 'status' => '1'];
                    }
                }

                $campaign->numbers = $numbersWithStatus;
            }


        }

        if($campaign->winner_collaborator_id){
            $numbers = explode(',', $campaign->winnerCollaborator->numbers);

            $formattedNumbers = array_map(function($number) {
                return '[' . $number . ']';
            }, $numbers);

            $campaign->winnerCollaborator->numbers = implode(' ', $formattedNumbers);
        }


        return $campaign;
    }


    /**
     * @param array $data
     * @return Campaign
     */
    public function create(array $data): Campaign
    {
        if (isset($data['image'])) {
            $imagePath = $this->imageHelper->storagePutB64Image(CAMPAIGN_PATH, $data['image']);
            $data['image'] = asset(str_replace('public', 'storage', $imagePath));
        }

        $data['status'] = 0;
        $data['uuid'] = Str::uuid();

        if($data['amount_tickets'] >= 2000){
            $data['ticket_filter_id'] = 2;
        }
        //$data['max_ticket'] = $data['max_ticket'] > 1000 ? 1000 : $data['max_ticket'];
        $campaign = Campaign::create($data);
        //$this->generateTickets($campaign);

        // Obtenha o ID da campanha recém-criada
        $campaignId = $campaign->id;

        // Atualize o campo de URL com base no nome da campanha e no ID
        $campaign->url = $campaign->url . $this->cleanString($data['name']) . '-' . $campaignId;
        $campaign->save();

        return $campaign;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Campaign
     * @throws \Exception
     */
    public function update(int $id, array $data): Campaign
    {
        $campaign = $this->getById($id);

        if (array_key_exists('image', $data)) {
            if ($data['image']) {
                // Exclua a imagem antiga, se existir
                if($campaign->image) {
                    // Use parse_url() para obter o caminho da URL
                    $path = parse_url($campaign->image, PHP_URL_PATH);

                    // Use pathinfo() para obter o nome do arquivo
                    $filename = pathinfo($path, PATHINFO_BASENAME);
                    $this->imageHelper->deleteStorageFile(CAMPAIGN_PATH . '/' .$filename);
                }

                // Atualize o caminho da nova imagem
                $data['image'] = asset(str_replace('public', 'storage', $this->imageHelper->updateStorageFile(
                    CAMPAIGN_PATH,
                    $data,
                    $campaign->getAttributeValue('image') ? $campaign->getAttributeValue('image') : ""
                )));
            }
        }

        if(@$data['released_until_fee']){

            if($data['released_until_fee'] == 1) {
                $digits = $this->getDigits($campaign['amount_tickets']);
                if ($digits > 5) {
                    $data['released_until_fee'] = 0;
                }
            }
        }


        //$data['max_ticket'] = $data['max_ticket'] > 1000 ? 1000 : $data['max_ticket'];

        if(@$data['amount_tickets']) {
            if ($data['amount_tickets'] >= 2000) {
                $data['ticket_filter_id'] = 2;
            }
        }

        if(@$data['status']){
            $data['released_until_fee'] = 0;
        }

        if(@$campaign['status'] || @$campaign['released_until_fee']){
            $data['amount_tickets'] = $campaign['amount_tickets'];
            $data['price_each_ticket'] = $campaign['price_each_ticket'];
        }

        $campaign->update($data);
        return $campaign;
    }



    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $campaign = Campaign::find($id);
        if($campaign->image) {
            // Use parse_url() para obter o caminho da URL
            $path = parse_url($campaign->image, PHP_URL_PATH);

            // Use pathinfo() para obter o nome do arquivo
            $filename = pathinfo($path, PATHINFO_BASENAME);
            $this->imageHelper->deleteStorageFile(CAMPAIGN_PATH . '/' .$filename);
        }

        return $campaign->delete();
    }

    // Função para gerar números aleatórios e salvar em um arquivo
    public function generateAndSaveRandomNumbers($campaign) {
        $quantity = $campaign->amount_tickets;


        $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);

        // Verificar se a pasta do usuário existe, se não, criar
        if (!File::exists($userFolder)) {
            File::makeDirectory($userFolder, 0755, true);
        }

        $filePath = $userFolder . '/' . $campaign->uuid . '.txt';

        // Verificar se o arquivo existe, se não, criar
        if (!File::exists($filePath)) {
            $maxNumber = $quantity - 1;

            try {

                $file = fopen($filePath, 'w');

                if(flock($file, LOCK_EX)) {
                    if ($file) {
                        $uniqueNumbers = [];
                        while (count($uniqueNumbers) < $quantity) {
                            $randomNumber = str_pad(mt_rand(0, $maxNumber), strlen($maxNumber), '0', STR_PAD_LEFT);
                            $uniqueNumbers[$randomNumber] = true;
                        }

                        fwrite($file, implode(',', array_keys($uniqueNumbers)));

                        flock($file, LOCK_UN);

                    } else {
                        Log::info('Erro ao abrir aquivo.');
                    }
                }else{
                    Log::info('Erro ao bloquear aquivo.');
                }
                fclose($file);
            }catch (Exception $e){
                Log::info($e);
            }
        }

    }

    public function generateAndSaveAllCombinations($campaign) {
        $amount_total = 80;
        $dezenas = 4;
        $quantity = $campaign->amount_tickets;

        $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);

        // Verificar se a pasta do usuário existe, se não, criar
        if (!File::exists($userFolder)) {
            File::makeDirectory($userFolder, 0755, true);
        }

        $filePath = $userFolder . '/' . $campaign->uuid . '.txt';

        // Verificar se o arquivo existe, se não, criar
        if (!File::exists($filePath)) {
            try {
                $file = fopen($filePath, 'w');

                if(flock($file, LOCK_EX)) {
                    if ($file) {
                        // Inicializa um array para armazenar as combinações
                        $combinations = [];

                        // Loop para gerar todas as combinações possíveis
                        for ($i = 1; $i <= $amount_total - $dezenas + 1; $i++) {
                            for ($j = $i + 1; $j <= $amount_total - $dezenas + 2; $j++) {
                                for ($k = $j + 1; $k <= $amount_total - $dezenas + 3; $k++) {
                                    for ($l = $k + 1; $l <= $amount_total; $l++) {
                                        $combination = sprintf('%02d,%02d,%02d,%02d', $i, $j, $k, $l);
                                        $combinations[] = $combination;
                                    }
                                }
                            }
                        }

                        // Escreve as combinações no arquivo
                        fwrite($file, implode(';', $combinations));

                        flock($file, LOCK_UN);

                    } else {
                        Log::info('Erro ao abrir arquivo.');
                    }
                } else {
                    Log::info('Erro ao bloquear arquivo.');
                }
                fclose($file);
            } catch (Exception $e) {
                Log::info($e);
            }
        }
    }


    public function re_generateAndSaveRandomNumbers($campaign) {

        $quantity = $campaign->amount_tickets;
        $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);

        // Verificar se a pasta do usuário existe, se não, criar
        if (!File::exists($userFolder)) {
            File::makeDirectory($userFolder, 0755, true);
        }

        $filePath = $userFolder . '/' . $campaign->uuid . '.txt';

        // Verificar se o arquivo existe, se não, criar
        if (!File::exists($filePath)) {
            $maxNumber = $quantity - 1;
            try {

                $file = fopen($filePath, 'w');

                if(flock($file, LOCK_EX)) {
                    if ($file) {
                        $uniqueNumbers = [];
                        while (count($uniqueNumbers) < $quantity) {
                            $randomNumber = str_pad(mt_rand(0, $maxNumber), strlen($maxNumber), '0', STR_PAD_LEFT);
                            $uniqueNumbers[$randomNumber] = true;
                        }

                        fwrite($file, implode(',', array_keys($uniqueNumbers)));

                        flock($file, LOCK_UN);

                        return true;
                    } else {
                        return false;
                    }
                }else{
                    Log::info('Erro ao bloquear aquivo.');
                }
                fclose($file);
            }catch (Exception $e){
                Log::info($e);
            }
        }

    }

    public function deleteRandomNumbersFile($campaign)
    {

        $filePath = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid . '/' . $campaign->uuid . '.txt');

        if (File::exists($filePath)) {
            try {
                File::delete($filePath);
            } catch (\Exception $e) {
                throw new \Exception(__('Erro ao deletar o arquivo: ' . $e->getMessage()), 404);
            }
        } else {
            throw new \Exception(__('Arquivo não encontrado'), 404);
        }
    }


    private function checkFileForDuplicatesInBatches($campaign, $batchSize = 10000) {
        $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $campaign->user->uuid);
        $filePath = $userFolder . '/' . $campaign->uuid . '.txt';

        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $numbers = explode(',', $content);

            $totalNumbers = count($numbers);
            $hasDuplicates = false;

            for ($i = 0; $i < $totalNumbers; $i += $batchSize) {
                $batch = array_slice($numbers, $i, $batchSize);

                // Verificar duplicatas no lote
                if (count($batch) !== count(array_unique($batch))) {
                    $hasDuplicates = true;
                    break;  // Se houver duplicatas, não é necessário verificar o restante
                }

                // Verificar duplicatas com números anteriores
                for ($j = 0; $j < $i; $j += $batchSize) {
                    $previousBatch = array_slice($numbers, $j, $batchSize);
                    $intersection = array_intersect($batch, $previousBatch);

                    if (!empty($intersection)) {
                        $hasDuplicates = true;
                        break 2;  // Se houver duplicatas, não é necessário verificar o restante
                    }
                }
            }

            return [
                'has_duplicates' => $hasDuplicates,
                'total_numbers' => $totalNumbers,
            ];
        }

        return null; // Arquivo não encontrado
    }

    public function generateTickets($campaign)
    {

        $digits = $this->getDigits($campaign->amount_tickets);

        if($digits >= 5){

            // Verificar se já foram gerados os bilhetes iniciais
            if($campaign->amount_tickets == $campaign->available_tickets) {
                $initialTicketsGenerated = Ticket::where('campaign_id', $campaign->id)->count();
                if($initialTicketsGenerated == 0) {
                    $this->generateOnDemandSequentialTickets($campaign, $initialTicketsGenerated);
                }
            }else {
                $totalTicketsGenerated = Ticket::where('campaign_id', $campaign->id)->count();

                if($totalTicketsGenerated < $campaign->amount_tickets) {

                    $reserved = $campaign->unavailable_tickets + $campaign->pending_tickets;

                    $percent_gen = 0.05;

                    if($digits == 5){
                        $percent_gen = $percent_gen;//5%
                    }else if($digits == 6){
                        $percent_gen = 0.015;//1.5%
                    }else if($digits == 7){
                        $percent_gen = 0.0075;//0.75%
                    }

                    $threshold = $totalTicketsGenerated * $percent_gen;

                    if ($reserved >= $threshold) {
                        $this->generateOnDemandSequentialTickets($campaign, $totalTicketsGenerated);
                    }
                }
            }
        }else {
            $totalTicketsGenerated = Ticket::where('campaign_id', $campaign->id)->count();
            if($totalTicketsGenerated == 0) {
                $this->generateSequentialTickets($campaign);
            }
        }
    }

    private function generateSequentialTickets($campaign)
    {
        $tickets = [];
        $digits = $this->getDigits($campaign->amount_tickets);
        $now = Carbon::now();

        for ($i = 0; $i < $campaign->amount_tickets; $i++) {
            $number = str_pad($i, $digits, '0', STR_PAD_LEFT);
            $tickets[] = [
                'campaign_id' => $campaign->id,
                'number' => $number,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        if (!empty($tickets)) {
            $chunks = collect($tickets)->chunk(500);

            foreach ($chunks as $chunk) {
                Ticket::insert($chunk->toArray());
            }
        }
    }

    private function generateOnDemandSequentialTickets($campaign, $startNumber)
    {
        $tickets = [];
        $digits = $this->getDigits($campaign->amount_tickets);
        $now = Carbon::now();

        if($digits == 5) {
            $howMuchGenerate = $campaign->amount_tickets / 10;
        }else if($digits == 6){
            if($startNumber < 200000) {
                $howMuchGenerate = $campaign->amount_tickets / 10;
            }else{
                $howMuchGenerate = $campaign->amount_tickets / 20;
            }
        }else if($digits == 7){
            $howMuchGenerate = $campaign->amount_tickets / 100;
        }

        $totalTicketsGenerate  = ($startNumber + $howMuchGenerate);

        if(($campaign->amount_tickets - $startNumber) < $howMuchGenerate){
            $totalTicketsGenerate = ($startNumber - $campaign->amount_tickets);
        }

        for ($i = $startNumber; $i < $totalTicketsGenerate; $i++) {
            $number = str_pad($i, $digits, '0', STR_PAD_LEFT);
            $tickets[] = [
                'campaign_id' => $campaign->id,
                'number' => $number,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        if (!empty($tickets)) {
            $chunks = collect($tickets)->chunk(500);

            foreach ($chunks as $chunk) {
                Ticket::insert($chunk->toArray());
            }
        }
    }

    private function getDigits($number)
    {
        return strlen((string)$number) - 1;
    }

    private function cleanString($string) {
        // Remove espaços em branco no início e no fim da string
        $string = trim($string);

        // Remove caracteres especiais e espaços
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

        return $string;
    }

}
