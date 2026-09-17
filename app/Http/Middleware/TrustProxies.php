<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Cloud Run sits behind Google's load balancer, which sets X-Forwarded-For.
     * Trusting proxies here ensures $request->ip() resolves to the visitor IP.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        // Not X-Forwarded-Host: the load balancer preserves Host, and with every
        // client trusted as a proxy that header would let anyone set the host that
        // generated links (password reset emails included) are built from.
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
