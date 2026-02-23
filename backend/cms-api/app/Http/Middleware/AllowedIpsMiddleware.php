<?php

namespace App\Http\Middleware;

use App\Traits\SAAApiResponse;
use Closure;

class AllowedIpsMiddleware
{
    use SAAApiResponse;

    public function handle($request, Closure $next)
    {
        $clientIp = $request->ip();
        $clientHost = parse_url($request->headers->get('referer'), PHP_URL_HOST);

        $allowedHosts = explode(',', config('client.api.private.allowed_hosts', ''));

        if (!in_array($clientIp, $allowedHosts) && !in_array($clientHost, $allowedHosts)) {
            return $this->errorResponse("Host {$clientHost} and or IP {$clientIp} are not allowed!", 403);
        }

        return $next($request);
    }
}
