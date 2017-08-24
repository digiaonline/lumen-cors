<?php namespace Nord\Lumen\Cors;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Nord\Lumen\Cors\Exceptions\InvalidArgument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;

class CorsService implements CorsServiceContract
{

    /**
     * Allowed request origins.
     *
     * @var array
     */
    private $allowOrigins = [];

    /**
     * Allowed HTTP methods.
     *
     * @var array
     */
    private $allowMethods = [];

    /**
     * Allowed HTTP headers.
     *
     * @var array
     */
    private $allowHeaders = [];

    /**
     * Whether or not the response can be exposed when credentials are present.
     *
     * @var bool
     */
    private $allowCredentials = false;

    /**
     * HTTP Headers that are allowed to be exposed to the web browser.
     *
     * @var array
     */
    private $exposeHeaders = [];

    /**
     * Indicates how long preflight request can be cached.
     *
     * @var int
     */
    private $maxAge = 0;

    /**
     * Creates the response if the origin is not allowed.
     *
     * @var Callable
     */
    private $originNotAllowed;

    /**
     * Creates the response if the method is not allowed.
     *
     * @var Callable
     */
    private $methodNotAllowed;

    /**
     * Creates the response if the header is not allowed.
     *
     * @var Callable
     */
    private $headerNotAllowed;


    /**
     * CorsService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
    }


    /**
     * @inheritdoc
     */
    public function handlePreflightRequest(Request $request)
    {
        try {
            $this->validatePreflightRequest($request);
        } catch (HttpResponseException $e) {
            return $this->createResponse($request, $e->getResponse());
        }

        return $this->createPreflightResponse($request);
    }


    /**
     * @inheritdoc
     */
    public function handleRequest(Request $request, Closure $next)
    {
        try {
            $this->validateRequest($request);
        } catch (HttpResponseException $e) {
            return $this->createResponse($request, $e->getResponse());
        }

        return $this->createResponse($request, $next($request));
    }


    /**
     * @inheritdoc
     */
    public function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin');
    }


    /**
     * @inheritdoc
     */
    public function isPreflightRequest(Request $request)
    {
        return $this->isCorsRequest($request) && $request->isMethod('OPTIONS') && $request->headers->has('Access-Control-Request-Method');
    }


    /**
     * Configures the service.
     *
     * @param array $config
     */
    protected function configure(array $config)
    {
        if (isset($config['allow_origins'])) {
            $this->setAllowOrigins($config['allow_origins']);
        }

        if (isset($config['allow_headers'])) {
            $this->setAllowHeaders($config['allow_headers']);
        }

        if (isset($config['allow_methods'])) {
            $this->setAllowMethods($config['allow_methods']);
        }

        if (isset($config['allow_credentials'])) {
            $this->setAllowCredentials($config['allow_credentials']);
        }

        if (isset($config['expose_headers'])) {
            $this->setExposeHeaders($config['expose_headers']);
        }

        if (isset($config['max_age'])) {
            $this->setMaxAge($config['max_age']);
        }

        if (isset($config['origin_not_allowed'])) {
            $this->setOriginNotAllowed($config['origin_not_allowed']);
        }

        if (isset($config['method_not_allowed'])) {
            $this->setMethodNotAllowed($config['method_not_allowed']);
        }

        if (isset($config['header_not_allowed'])) {
            $this->setHeaderNotAllowed($config['header_not_allowed']);
        }
    }


    /**
     * @param Request $request
     *
     * @throws InvalidArgument
     * @throws HttpResponseException
     */
    protected function validatePreflightRequest(Request $request)
    {
        $origin = $request->headers->get('Origin');

        if (!$this->isOriginAllowed($origin)) {
            throw new HttpResponseException($this->createOriginNotAllowedResponse($request));
        }

        $method = $request->headers->get('Access-Control-Request-Method');

        if ($method && !$this->isMethodAllowed($method)) {
            throw new HttpResponseException($this->createMethodNotAllowedResponse($request));
        }

        if (!$this->isAllHeadersAllowed()) {
            $headers = str_replace(' ', '', $request->headers->get('Access-Control-Request-Headers'));

            if (is_string($headers)) {
                foreach (explode(',', $headers) as $header) {
                    if (!$this->isHeaderAllowed($header)) {
                        throw new HttpResponseException($this->createHeaderNotAllowedResponse($request));
                    }
                }
            }
        }
    }


    /**
     * Creates a preflight response.
     *
     * @param Request $request
     *
     * @return Response
     */
    protected function createPreflightResponse(Request $request)
    {
        $response = new Response();

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->maxAge) {
            $response->headers->set('Access-Control-Max-Age', $this->maxAge);
        }

        $allowMethods = $this->isAllMethodsAllowed()
            ? strtoupper($request->headers->get('Access-Control-Request-Method'))
            : implode(', ', $this->allowMethods);

        $response->headers->set('Access-Control-Allow-Methods', $allowMethods);

        $allowHeaders = $this->isAllHeadersAllowed()
            ? strtolower($request->headers->get('Access-Control-Request-Headers'))
            : implode(', ', $this->allowHeaders);

        $response->headers->set('Access-Control-Allow-Headers', $allowHeaders);

        return $response;
    }


    /**
     * @param Request $request
     *
     * @throws InvalidArgument
     */
    protected function validateRequest(Request $request)
    {
        $origin = $request->headers->get('Origin');

        if (!$this->isOriginAllowed($origin)) {
            throw new HttpResponseException($this->createOriginNotAllowedResponse($request));
        }
    }


    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function createResponse(Request $request, Response $response)
    {
        $origin = $request->headers->get('Origin');

        $response->headers->set('Access-Control-Allow-Origin', $origin);

        $vary = $request->headers->has('Vary') ? $request->headers->get('Vary') . ', Origin' : 'Origin';
        $response->headers->set('Vary', $vary);

        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if (!empty($this->exposeHeaders)) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposeHeaders));
        }

        return $response;
    }


    /**
     * @inheritdoc
     */
    protected function createOriginNotAllowedResponse(Request $request)
    {
        return $this->originNotAllowed
            ? call_user_func($this->originNotAllowed, $request)
            : $this->createErrorResponse('Origin not allowed.', 403);
    }


    /**
     * @inheritdoc
     */
    protected function createMethodNotAllowedResponse(Request $request)
    {
        return $this->methodNotAllowed
            ? call_user_func($this->methodNotAllowed, $request)
            : $this->createErrorResponse('Method not allowed.', 405);
    }


    /**
     * @inheritdoc
     */
    protected function createHeaderNotAllowedResponse(Request $request)
    {
        return $this->headerNotAllowed
            ? call_user_func($this->headerNotAllowed, $request)
            : $this->createErrorResponse('Header not allowed.', 403);
    }


    /**
     * Creates an error response.
     *
     * @param string $content
     * @param int    $status
     *
     * @return Response
     */
    protected function createErrorResponse($content = '', $status = 400)
    {
        return new Response($content, $status);
    }


    /**
     * Returns whether or not the origin is allowed.
     *
     * @param mixed $origin
     *
     * @return bool
     *
     * @throws InvalidArgument
     */
    protected function isOriginAllowed($origin)
    {
        if (!is_string($origin) || empty($origin)) {
            throw new InvalidArgument('Origin must be non empty string.');
        }

        if ($this->isAllOriginsAllowed()) {
            return true;
        }

        return in_array($origin, $this->allowOrigins);
    }


    /**
     * Returns whether or not the method is allowed.
     *
     * @param mixed $method
     *
     * @return bool
     *
     * @throws InvalidArgument
     */
    protected function isMethodAllowed($method)
    {
        if (!is_string($method) || empty($method)) {
            throw new InvalidArgument('Method must be a non empty string.');
        }

        if ($this->isAllMethodsAllowed()) {
            return true;
        }

        return in_array(strtoupper($method), $this->allowMethods);
    }


    /**
     * Returns whether or not the header is allowed.
     *
     * @param mixed $header
     *
     * @return bool
     *
     * @throws InvalidArgument
     */
    protected function isHeaderAllowed($header)
    {
        if (!is_string($header) || empty($header)) {
            throw new InvalidArgument('Header must be a non empty string.');
        }

        if ($this->isAllHeadersAllowed()) {
            return true;
        }

        return in_array(strtolower($header), $this->allowHeaders);
    }


    /**
     * @return bool
     */
    protected function isAllOriginsAllowed()
    {
        return in_array('*', $this->allowOrigins);
    }


    /**
     * @return bool
     */
    protected function isAllMethodsAllowed()
    {
        return in_array('*', $this->allowMethods);
    }


    /**
     * @return bool
     */
    protected function isAllHeadersAllowed()
    {
        return in_array('*', $this->allowHeaders);
    }


    /**
     * @param array $allowOrigins
     */
    protected function setAllowOrigins(array $allowOrigins)
    {
        $this->allowOrigins = $allowOrigins;
    }


    /**
     * @param array $allowMethods
     */
    protected function setAllowMethods(array $allowMethods)
    {
        $this->allowMethods = array_map('strtoupper', $allowMethods);
    }


    /**
     * @param array $allowHeaders
     */
    protected function setAllowHeaders(array $allowHeaders)
    {
        $this->allowHeaders = array_map('strtolower', $allowHeaders);
    }


    /**
     * @param array $exposeHeaders
     */
    protected function setExposeHeaders(array $exposeHeaders)
    {
        $this->exposeHeaders = array_map('strtolower', $exposeHeaders);
    }


    /**
     * @param boolean $allowCredentials
     *
     * @throws InvalidArgument
     */
    protected function setAllowCredentials($allowCredentials)
    {
        if (!is_bool($allowCredentials)) {
            throw new InvalidArgument('Allow credentials must be a boolean.');
        }

        $this->allowCredentials = $allowCredentials;
    }


    /**
     * @param int $maxAge
     *
     * @throws InvalidArgument
     */
    protected function setMaxAge($maxAge)
    {
        if (!is_int($maxAge)) {
            throw new InvalidArgument('Max age must be an integer.');
        }

        if ($maxAge < 0) {
            throw new InvalidArgument('Max age must be a positive number or zero.');
        }

        $this->maxAge = $maxAge;
    }


    /**
     * @param Callable $originNotAllowed
     *
     * @throws InvalidArgument
     */
    protected function setOriginNotAllowed($originNotAllowed)
    {
        if (!is_callable($originNotAllowed)) {
            throw new InvalidArgument('Origin not allowed must be a callable.');
        }

        $this->originNotAllowed = $originNotAllowed;
    }


    /**
     * @param Callable $methodNotAllowed
     *
     * @throws InvalidArgument
     */
    protected function setMethodNotAllowed($methodNotAllowed)
    {
        if (!is_callable($methodNotAllowed)) {
            throw new InvalidArgument('Method not allowed must be a callable.');
        }

        $this->methodNotAllowed = $methodNotAllowed;
    }


    /**
     * @param Callable $headerNotAllowed
     *
     * @throws InvalidArgument
     */
    protected function setHeaderNotAllowed($headerNotAllowed)
    {
        if (!is_callable($headerNotAllowed)) {
            throw new InvalidArgument('Header not allowed must be a callable.');
        }

        $this->headerNotAllowed = $headerNotAllowed;
    }
}
