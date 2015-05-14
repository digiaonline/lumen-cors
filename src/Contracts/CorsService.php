<?php namespace Nord\Lumen\Cors\Contracts;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface CorsService
{

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handlePreflightRequest(Request $request);


    /**
     * @param Request $request
     * @param         $response
     *
     * @return Response
     */
    public function handleRequest(Request $request, Response $response);


    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isCorsRequest(Request $request);


    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isPreflightRequest(Request $request);


    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isRequestAllowed(Request $request);

}
