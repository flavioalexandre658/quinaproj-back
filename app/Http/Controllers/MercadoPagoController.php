<?php

namespace App\Http\Controllers;
use App\Models\Campaign;
use App\Models\Collaborator;
use App\Models\PaymentMethod;
use App\Models\Payment as PayBD;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Interfaces\CampaignRepositoryInterface;
use App\Interfaces\CollaboratorRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\Payment;
use MercadoPago\MerchantOrder;
use Ramsey\Uuid\Uuid;


class MercadoPagoController extends Controller
{

    public function processPaymentCheckoutPro(Request $request)
    {
        $data = $request->validate([
            'user_id'  => 'int|exists:users,id|nullable',
            'collaborator_id'  => 'int|exists:collaborators,id|nullable',
            'campaign_id'  => 'required|int|exists:campaigns,id'
        ]);

        $access_token = config('services.mercadopago.access_token');
        $campaign = Campaign::where('id', $data['campaign_id'])->first();

        if(@$data['collaborator_id']) {
            $paymentMethods = PaymentMethod::where('user_id', $campaign['user_id'])->get();

            //$paymentMethods = json_decode($paymentMethods);

            foreach ($paymentMethods as $payMethod) {
                if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                    $access_token = $payMethod->api_token;
                }
            }
        }

        if (isset($data['collaborator_id'])) {
            $collaborator = Collaborator::where('id', $data['collaborator_id'])->first();
            $price_ea_ticket = str_replace("R$", "", $collaborator['price_each_ticket']);
            $price_ea_ticket = (float)str_replace(",", ".", $price_ea_ticket);
            $amount = $price_ea_ticket * $collaborator['amount_of_tickets'];
            $data_person = $collaborator;
        }

        if (isset($data['user_id'])){
            $user = User::where('id',$data['user_id'])->first();
            $amount = str_replace("R$", "", $campaign['fee']['fee']);
            $amount = (float)str_replace(",", ".", $amount);

            if (is_numeric($user['discount'])) {
                // Garante que $user['discount'] seja um número
                $discount = floatval($user['discount']);

                // Aplica o desconto se for um número válido
                $amount = $amount - ($amount * ($discount / 100));
            }

            $data_person = $user;
        }

        $url = 'https://api.mercadopago.com/checkout/preferences';

        $data = [
            "items" => [
                [
                    "id" => $campaign['id'],
                    "title" => "Pagamento ". $campaign['name'],
                    "currency_id" => "BRL",
                    "picture_url" => $campaign['image'],
                    "description" => $data_person['email'],
                    "category_id" => "software service",
                    "quantity" => 1,
                    "unit_price" => ceil($amount * 100) / 100
                ]
            ],
            "payer" => [
                "id" => $data_person['id'],
                "name" => $data_person['name'],
                "email" => $data_person['email'],
            ],
            "back_urls" => [
                "success" => "https://app.123rifas.com/voucher/".$campaign['uuid'],
                "failure" => "https://app.123rifas.com/voucher/".$campaign['uuid'],
                "pending" => "https://app.123rifas.com/voucher/".$campaign['uuid']
            ],
            "payment_methods" => [
                "excluded_payment_methods" => [
                    ["id" => "pix"],
                    ["id" => "bolbradesco"],
                    ["id" => "pec"]
                ],
                "excluded_payment_types" => [
                    ["id" => "debit_card"]
                ],
                "installments" => 12
            ],
            "notification_url" => "https://api.123rifas.com/api/mercadopago/callback?source_news=webhook",
            "statement_descriptor" => "123RIFAS",
            "external_reference" => "123rifas_ref",
            "expires" => false,
           // "expiration_date_from" => "2016-02-01T12:00:00.000-04:00",
          //  "expiration_date_to" => "2016-02-28T12:00:00.000-04:00"
        ];

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            return 'Curl error: ' . curl_error($ch);
        }

        return response()->json([
            'payment' => json_decode($response, true)
        ]);
    }

    public function processPaymentPix(Request $request)
    {
        $data = $request->validate([
            'user_id'  => 'int|exists:users,id|nullable',
            'collaborator_id'  => 'int|exists:collaborators,id|nullable',
            'campaign_id'  => 'required|int|exists:campaigns,id'
        ]);

        $access_token = config('services.mercadopago.access_token');
        $campaign = Campaign::where('id', $data['campaign_id'])->first();

        if(@$data['collaborator_id']) {
            $paymentMethods = PaymentMethod::where('user_id', $campaign['user_id'])->get();
            //$paymentMethods = json_decode($paymentMethods);

            foreach ($paymentMethods as $payMethod) {
                if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                    $access_token = $payMethod->api_token;
                }
            }
        }

        $url = 'https://api.mercadopago.com/v1/payments';

        if (isset($data['collaborator_id'])) {
            $collaborator = Collaborator::where('id', $data['collaborator_id'])->first();
            $price_ea_ticket = str_replace("R$", "", $collaborator['price_each_ticket']);
            $price_ea_ticket = (float)str_replace(",", ".", $price_ea_ticket);
            $amount = $price_ea_ticket * $collaborator['amount_of_tickets'];
            $data_person = $collaborator;
        }

        if (isset($data['user_id'])){
            $user = User::where('id',$data['user_id'])->first();
            $amount = str_replace("R$", "", $campaign['fee']['fee']);
            $amount = (float)str_replace(",", ".", $amount);

            if (is_numeric($user['discount'])) {
                // Garante que $user['discount'] seja um número
                $discount = floatval($user['discount']);

                // Aplica o desconto se for um número válido
                $amount = $amount - ($amount * ($discount / 100));
            }

            $data_person = $user;
        }

        $data =
            [
                "transaction_amount" => ceil($amount * 100) / 100,
                "description" => "Pagamento ". $campaign['name'],
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => $data_person['email'],
                    "first_name" => $data_person['name']
                ],
                "notification_url" => "https://api.123rifas.com/api/mercadopago/callback?source_news=webhook"
            ];
        $idempotency_key = Uuid::uuid4(); // Gera um UUID versão 4 aleatório
        $header = array(
            "Content-Type: application/json",
            "Cache-Control: application/json",
            "Authorization: Bearer ".$access_token,
            'X-Idempotency-Key:' .$idempotency_key,
        );

        $connect = curl_init ();

        curl_setopt ($connect, CURLOPT_URL, $url);
        curl_setopt ($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($connect, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($connect, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($connect, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($connect, CURLOPT_POST, true);
        curl_setopt ($connect, CURLOPT_POSTFIELDS, json_encode($data));


        $request = curl_exec ($connect);

        if ($request == false){
            return 'Error: '.curl_error ($connect);
        }

        return response()->json([
            'payment' => json_decode($request, true)
        ]);
    }

    public function oAuthToken(Request $request)
    {
        $data = $request->validate([
            'code'  => 'string|required',
            'state'  => 'string'
        ]);

        $access_token = config('services.mercadopago.access_token');

        $userUuid = $data['state'];
        $url = 'https://api.mercadopago.com/oauth/token';
        $client_id = 3759480538384100;
        $client_secret = '0HxbLSP1Da5SJSDjsNwJwXLpXIp4Mlpt';
        $code = $data['code'];
        $grant_type = 'authorization_code';


        $data =
            [
                "client_id" => $client_id,
                "client_secret" => $client_secret,
                "code" => $code,
                "grant_type" => $grant_type,
                'redirect_uri' => 'https://app.123rifas.com/load'
            ];

        $idempotency_key = Uuid::uuid4(); // Gera um UUID versão 4 aleatório

        $header = array(
            "Content-Type: application/json",
            "Cache-Control: application/json",
            "Authorization: Bearer ".$access_token,
            'X-Idempotency-Key:' .$idempotency_key,
        );

        $connect = curl_init ();

        curl_setopt ($connect, CURLOPT_URL, $url);
        curl_setopt ($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($connect, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($connect, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($connect, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($connect, CURLOPT_POST, true);
        curl_setopt ($connect, CURLOPT_POSTFIELDS, json_encode($data));


        $request = curl_exec ($connect);

        if ($request == false){
            return 'Error: '.curl_error ($connect);
        }


        $response_mp = json_decode($request, true);
        $hasMp = false;
        $pm = [];

        $user_id_mp = $response_mp['user_id'];

        //GET USER MP
        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $response_mp['access_token'],
            'Accept: application/json',
        );

        curl_setopt($ch, CURLOPT_URL, "https://api.mercadolibre.com/users/$user_id_mp");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $userMP = json_decode(curl_exec($ch), true);

        curl_close($ch);

        //END GET USER MP

        if ($userUuid && $response_mp['access_token']) {
            $user = User::where('uuid', $userUuid)->first();
            $paymentMethods = PaymentMethod::where('user_id', $user['id'])
                ->get();



            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->name_method === "Mercado Pago") {
                    // Atualiza o token da API para o método de pagamento "Mercado Pago" existente
                    $paymentMethod->api_token = $response_mp['access_token'];
                    $paymentMethod->refresh_token = $response_mp['refresh_token'];
                    $paymentMethod->expire_in = $response_mp['expires_in'];
                    $paymentMethod->user_connected = $userMP['nickname'];
                    $paymentMethod->email_connected = $userMP['email'];
                    $paymentMethod->status = true;
                    $paymentMethod->save();
                    $pm = $paymentMethod;
                    $hasMp = true;
                    break;
                }
            }

            if (!$hasMp) {
                // Cria um novo método de pagamento "Mercado Pago" se não existir
                $pm = PaymentMethod::create([
                    'user_id' => $user->id,
                    'name_method' => 'Mercado Pago', // Certifique-se de que o nome do método esteja correto
                    'status' => true,
                    'api_token' => $response_mp['access_token'],
                    'refresh_token' => $response_mp['refresh_token'],
                    'expire_in' => $response_mp['expires_in'],
                    'user_connected' => $response_mp['nickname'],
                    'email_connected' => $response_mp['email'],
                ]);
            }
        }


        return response()->json([
            'oAuth' => $response_mp,
            'payment_method' => (bool)$pm,
            'userMP' => (bool)$userMP
        ]);
    }

    public function getPaymentById(int $payment_id)
    {
        // Ler os dados de entrada como JSON
        //$input = file_get_contents('php://input');
        //$data = json_decode($input, true);

        // Buscar informações do pagamento no Mercado Pago
        $access_token = config('services.mercadopago.access_token');

        $payment = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
            ->where('transaction_id', $payment_id)
            ->first();

        if(@$payment['collaborator_id']) {
            $paymentMethods = PaymentMethod::where('user_id', $payment['campaign']['user_id'])->get();
            //$paymentMethods = json_decode($paymentMethods);

            foreach ($paymentMethods as $payMethod) {
                if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                    $access_token = $payMethod->api_token;
                }
            }
        }

        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json',
        );

        Log::error($payment_id);
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $payment = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return response()->json([
            'payment' => $payment
        ]);
    }

    public function getPreferenceById(string $payment_id)
    {
        // Ler os dados de entrada como JSON
        //$input = file_get_contents('php://input');
        //$data = json_decode($input, true);

        // Buscar informações do pagamento no Mercado Pago
        $access_token = config('services.mercadopago.access_token');

        $payment = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
            ->where('transaction_id', $payment_id)
            ->first();

        if(@$payment['collaborator_id']) {

            $paymentMethods = PaymentMethod::where('user_id', $payment['campaign']['user_id'])
                ->get();
            //$paymentMethods = json_decode($paymentMethods);

            foreach ($paymentMethods as $payMethod) {
                if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                    $access_token = $payMethod->api_token;
                }
            }
        }

        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json',
        );


        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/checkout/preferences/$payment_id");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $payment = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return response()->json([
            'preference' => $payment
        ]);
    }


    public function callbackPayment()
    {

    // Ler os dados de entrada como JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
      //  Log::info('PAYMENT: ' . json_encode($data));
    // Buscar informações do pagamento no Mercado Pago
    $access_token = config('services.mercadopago.access_token');
    if(@$data['action'] != 'payment.created' && @$data['type']) {
        if ($data["type"] != "test") {

            $payment = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
                ->where('transaction_id', $data["data"]["id"])
                ->first();

            if (@$payment['collaborator_id']) {

                $paymentMethods = PaymentMethod::where('user_id', $payment['campaign']['user_id'])
                    ->get();
                //$paymentMethods = json_decode($paymentMethods);

                foreach ($paymentMethods as $payMethod) {
                    if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                        $access_token = $payMethod->api_token;
                    }
                }
            }
        }

        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json',
        );

        switch ($data["type"]) {
            case "payment":
                $payment_id = $data["data"]["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $payment = json_decode(curl_exec($ch), true);
                if ($payment['status'] != 'pending') {

                    PayBD::where('transaction_id', $payment_id)
                        ->first()->update(['status' => $payment['status']]);
                }
                break;
          /*  case "plan":
                $plan_id = $data["data"]["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/plans/$plan_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $plan = json_decode(curl_exec($ch), true);
                print_r($plan);
                break;
            case "subscription":
                $subscription_id = $data["data"]["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/subscriptions/$subscription_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $subscription = json_decode(curl_exec($ch), true);
                print_r($subscription);
                break;
            case "invoice":
                $invoice_id = $data["data"]["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/invoices/$invoice_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $invoice = json_decode(curl_exec($ch), true);
                print_r($invoice);
                break;*/
            case "test":
                // Tratar o caso "test.created"
                if ($data["action"] == "test.created") {
                    // Seu código de tratamento aqui
                    echo "Teste criado com o id: " . $data["data"]["id"];
                }
                break;
        }

        curl_close($ch);
    }else if(@$data['action'] == 'payment.created' && $data['type']){

        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json',
        );

        switch ($data["type"]) {
            case "payment":
                $payment_id = $data["data"]["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $payment = json_decode(curl_exec($ch), true);

               /* $payment_bd = $this->PaymentRepository->getByTransactionId($payment_id);
                if(@$payment_bd) {
                    $this->PaymentRepository->updateByTransaction($payment_id, ['status' => $payment['status']]);
                }else {*/
                    $user = User::where('email', $payment['additional_info']['items'][0]['description'])->first();
                    PayBD::create([
                        'transaction_id' => $payment_id,
                        'amount' => $payment['transaction_amount'],
                        'currency' => $payment['currency_id'],
                        'status' => $payment['status'],
                        'user_id' => $user['id'],
                        'campaign_id' => $payment['additional_info']['items'][0]['id']
                    ]);
                //}

                break;
            /*  case "plan":
                  $plan_id = $data["data"]["id"];
                  curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/plans/$plan_id");
                  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  $plan = json_decode(curl_exec($ch), true);
                  print_r($plan);
                  break;
              case "subscription":
                  $subscription_id = $data["data"]["id"];
                  curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/subscriptions/$subscription_id");
                  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  $subscription = json_decode(curl_exec($ch), true);
                  print_r($subscription);
                  break;
              case "invoice":
                  $invoice_id = $data["data"]["id"];
                  curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/invoices/$invoice_id");
                  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  $invoice = json_decode(curl_exec($ch), true);
                  print_r($invoice);
                  break;*/
            case "test":
                // Tratar o caso "test.created"
                if ($data["action"] == "test.created") {
                    // Seu código de tratamento aqui
                    echo "Teste criado com o id: " . $data["data"]["id"];
                }
                break;
        }

        curl_close($ch);
    }
    }

    public function callbackPreference()
    {

        // Ler os dados de entrada como JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Buscar informações do pagamento no Mercado Pago
        $access_token = config('services.mercadopago.access_token');

        if(@$data['action'] != 'payment.created' && @$data['type']) {
            if ($data["type"] != "test") {

                $payment = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
                    ->where('transaction_id', $data["data"]["id"])
                    ->first();

                if (@$payment['collaborator_id']) {

                    $paymentMethods = PaymentMethod::where('user_id', $payment['campaign']['user_id'])
                        ->get();
                    //$paymentMethods = json_decode($paymentMethods);

                    foreach ($paymentMethods as $payMethod) {
                        if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                            $access_token = $payMethod->api_token;
                        }
                    }
                }
            }

            $ch = curl_init();
            $headers = array(
                'Authorization: Bearer ' . $access_token,
                'Accept: application/json',
            );

            switch ($data["type"]) {
                case "payment":
                    $payment_id = $data["data"]["id"];
                    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $payment = json_decode(curl_exec($ch), true);

                    if ($payment['status'] != 'pending') {
                        PayBD::where('transaction_id', $payment_id)
                            ->first()->update(['status' => $payment['status']]);
                    }
                    break;
                /*  case "plan":
                      $plan_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/plans/$plan_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $plan = json_decode(curl_exec($ch), true);
                      print_r($plan);
                      break;
                  case "subscription":
                      $subscription_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/subscriptions/$subscription_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $subscription = json_decode(curl_exec($ch), true);
                      print_r($subscription);
                      break;
                  case "invoice":
                      $invoice_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/invoices/$invoice_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $invoice = json_decode(curl_exec($ch), true);
                      print_r($invoice);
                      break;*/
                case "test":
                    // Tratar o caso "test.created"
                    if ($data["action"] == "test.created") {
                        // Seu código de tratamento aqui
                        echo "Teste criado com o id: " . $data["data"]["id"];
                    }
                    break;
            }

            curl_close($ch);
        }else if(@$data['action'] == 'payment.created' && $data['type']){

            $ch = curl_init();
            $headers = array(
                'Authorization: Bearer ' . $access_token,
                'Accept: application/json',
            );

            switch ($data["type"]) {
                case "payment":
                    $payment_id = $data["data"]["id"];
                    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $payment = json_decode(curl_exec($ch), true);

                    $payment_bd = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
                        ->where('transaction_id', $payment_id)
                        ->first();
                    if(@$payment_bd) {
                        PayBD::where('transaction_id', $payment_id)
                            ->first()->update(['status' => $payment['status']]);
                    }else {
                        $user = User::where('email', $payment['additional_info']['items'][0]['description'])->first();
                        PayBD::create([
                            'transaction_id' => $payment_id,
                            'amount' => $payment['transaction_amount'],
                            'currency' => $payment['currency_id'],
                            'status' => $payment['status'],
                            'user_id' => $user['id'],
                            'campaign_id' => $payment['additional_info']['items'][0]['id']
                        ]);
                    }

                    break;
                /*  case "plan":
                      $plan_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/plans/$plan_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $plan = json_decode(curl_exec($ch), true);
                      print_r($plan);
                      break;
                  case "subscription":
                      $subscription_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/subscriptions/$subscription_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $subscription = json_decode(curl_exec($ch), true);
                      print_r($subscription);
                      break;
                  case "invoice":
                      $invoice_id = $data["data"]["id"];
                      curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/invoices/$invoice_id");
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      $invoice = json_decode(curl_exec($ch), true);
                      print_r($invoice);
                      break;*/
                case "test":
                    // Tratar o caso "test.created"
                    if ($data["action"] == "test.created") {
                        // Seu código de tratamento aqui
                        echo "Teste criado com o id: " . $data["data"]["id"];
                    }
                    break;
            }

            curl_close($ch);
        }
    }

    public function ipn()
    {
        $access_token = config('services.mercadopago.access_token');
        $merchant_order = null;


        if($_GET["id"] != "123456") {

            $payment = PayBD::with(['campaign', 'campaign.raffle', 'user', 'campaign.user'])
                ->where('transaction_id', $_GET["id"])
                ->first();

            if (@$payment['collaborator_id']) {

                $paymentMethods = PaymentMethod::where('user_id', $payment['campaign']['user_id'])
                    ->get();

               // $paymentMethods = json_decode($paymentMethods);

                foreach ($paymentMethods as $payMethod) {
                    if ($payMethod->name_method == 'Mercado Pago' && $payMethod->status) {
                        $access_token = $payMethod->api_token;
                    }
                }
            }
        }

        $ch = curl_init();
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json',
            "Content-Type: application/json",
            "Cache-Control: application/json",
        );

        switch($_GET["topic"]) {
            case "payment":
                $payment_id = $_GET["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $payment = json_decode(curl_exec($ch), true);
               // return json_encode($payment);
                $merchant_order_id = $payment['order']['id'];

                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/merchant_orders/$merchant_order_id");
                $merchant_order = json_decode(curl_exec($ch), true);
                break;
            case "merchant_order":
                $merchant_order_id = $_GET["id"];
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/merchant_orders/$merchant_order_id");
                $merchant_order = json_decode(curl_exec($ch), true);
                break;
        }

        $paid_amount = 0;
        foreach ($merchant_order['payments'] as $payment) {
            if ($payment['status'] == 'approved'){
                $paid_amount += $payment['transaction_amount'];
            }

            if ($payment['status'] != 'pending' && $_GET["id"] != "123456") {

                PayBD::where('transaction_id', $_GET["id"])
                    ->first()->update(['status' => $payment['status']]);
            }
        }

        if($paid_amount >= $merchant_order['total_amount']){
            if (count($merchant_order['shipments'])>0) {
                if($merchant_order['shipments'][0]['status'] == "ready_to_ship") {
                    print_r("Totally paid. Print the label and release your item.");
                }
            } else {
                print_r("Totally paid. Release your item.");
            }
        } else {
            print_r("Not paid yet. Do not release your item.");
        }

        curl_close($ch);

    }

}
