<?php namespace Nord\Lumen\Cors\Middleware;


use Illuminate\Http\Response;
use Nord\Lumen\Cors\Contracts\CorsService;
use Illuminate\Http\Request;

class CorsMiddleware
{

	const CORS_REQUEST_NOT_ALLOWED = 'CORS request not allowed';

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
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle(Request $request, \Closure $next)
	{
		if ( ! $this->service->isCorsRequest($request)) {
			return $next($request);
		}

		if ($this->service->isPreflightRequest($request)) {
			return $this->service->handlePreflightRequest($request);
		}

		if ( ! $this->service->isRequestAllowed($request)) {
			return new Response(static::CORS_REQUEST_NOT_ALLOWED, 403);
		}

		return $this->service->handleRequest($request, $next($request));
	}
}
