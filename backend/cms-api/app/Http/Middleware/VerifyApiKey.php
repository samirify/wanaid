<?php

namespace App\Http\Middleware;

use App\Traits\SAAApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyApiKey
{
    use SAAApiResponse;

    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $apiKey = config('client.api.key');

        $apiKeyIsValid = (
            !empty($apiKey)
            && $request->header('x-api-key') == $apiKey
        );

        if (!$apiKeyIsValid)
            return $this->errorResponse('Access denied! Invalid API key', 403);

        return $next($request);
    }
}
