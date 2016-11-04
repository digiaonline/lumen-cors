<?php

namespace Nord\Lumen\Cors;

use Closure;
use Illuminate\Http\Request;
use Nord\Lumen\Cors\Contracts\CorsService;

class CorsMiddleware
{
    /**
     * @var CorsService
     */
    private $service;

    /**
     * CorsMiddleware constructor.
     *
     * @param CorsService $service
     */
    public function __construct(CorsService $service)
    {
        $this->service = $service;
    }

    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->service->isCorsRequest($request)) {
            return $next($request);
        }

        if ($this->service->isPreflightRequest($request)) {
            return $this->service->handlePreflightRequest($request);
        }

        return $this->service->handleRequest($request, $next($request));
    }
}
