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
     * Creates an origin not allowed response.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createOriginNotAllowedResponse(Request $request);


    /**
     * Creates a method not allowed response.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createMethodNotAllowedResponse(Request $request);


    /**
     * Creates a header not allowed response.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createHeaderNotAllowedResponse(Request $request);


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
