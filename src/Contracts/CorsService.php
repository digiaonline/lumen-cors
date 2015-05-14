<?php namespace Nord\Lumen\Cors\Contracts;

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
     * @param Response $response
     *
     * @return Response
     */
    public function handleRequest(Request $request, Response $response);


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


    /**
     * Returns whether or not the request is allowed.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isRequestAllowed(Request $request);

}
