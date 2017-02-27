<?php namespace Nord\Lumen\Cors\Contracts;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface CorsService
{

    /**
     * Handles a preflight request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handlePreflightRequest(Request $request);


    /**
     * Handles the actual request.
     *
     * @param Request  $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handleRequest(Request $request, Closure $next);


    /**
     * Returns whether or not the request is a CORS request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isCorsRequest(Request $request);


    /**
     * Returns whether or not the request is a preflight request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isPreflightRequest(Request $request);
}
