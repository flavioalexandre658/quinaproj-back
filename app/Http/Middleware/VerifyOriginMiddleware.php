<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;
use Closure;

class VerifyOriginMiddleware
{
    public function handle($request, Closure $next)
    {
        return $next($request);
        $allowedOrigins = ['172.67.207.27:443', 'https://172.67.207.27:443','http://192.168.0.106:8080', 'http://localhost:8080', 'postman-api-123rifas', 'http://123rifas.com', 'https://123rifas.com', 'https://api.123rifas.com', 'https://app.123rifas.com', 'https://mundilimos.postman.co']; // Adicione os domínios permitidos aqui

        $origin = $request->header('Origin');
        $userAgent = $request->header('user-agent');
        $proxyHeader = $request->header('X-Proxy-Header');
        //DEBUG
        //$headers = $request->headers->all();
        //Log::info('Headers da Requisição: ' . json_encode($headers));

        $originAllowed = false;
        $userAgentAllowed = false;
        $proxyAllowed = false;

        if($proxyHeader == 'auth'){
            $proxyAllowed = true;
        }

        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($origin, $allowedOrigin) !== false) {
                $originAllowed = true;
                break;
            }
        }

        // Array de strings de validação para o User-Agent
        $userAgentValidationStrings = ['MercadoPago'];

        foreach ($userAgentValidationStrings as $validationString) {
            if (strpos($userAgent, $validationString) !== false) {
                $userAgentAllowed = true;
                break;
            }
        }

        if ($originAllowed || $userAgentAllowed || $proxyAllowed) {
            // A requisição é de um domínio permitido ou o User-Agent contém uma das strings de validação
            return $next($request);
        } else {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }
    }
}

