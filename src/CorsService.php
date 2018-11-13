<?php namespace Nord\Lumen\Cors;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * CorsService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['allow_origins'])) {
            $this->allowOrigins = $config['allow_origins'];
        }

        if (isset($config['allow_headers'])) {
            $this->setAllowHeaders($config['allow_headers']);
        }

        if (isset($config['allow_methods'])) {
            $this->setAllowMethods($config['allow_methods']);
        }

        if (isset($config['allow_credentials'])) {
            $this->allowCredentials = $config['allow_credentials'];
        }

        if (isset($config['expose_headers'])) {
            $this->setExposeHeaders($config['expose_headers']);
        }

        if (isset($config['max_age'])) {
            $this->setMaxAge($config['max_age']);
        }
    }


    /**
     * @inheritdoc
     */
    public function handlePreflightRequest(Request $request): Response
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
    public function handleRequest(Request $request, Response $response): Response
    {
        return $this->createResponse($request, $response);
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
     * @param Request $request
     *
     * @throws HttpResponseException
     */
    protected function validatePreflightRequest(Request $request)
    {
        $origin = $request->headers->get('Origin');

        if (!$this->isOriginAllowed($origin)) {
            throw new HttpResponseException(new Response('Origin not allowed', 403));
        }

        $method = $request->headers->get('Access-Control-Request-Method');

        if ($method && !$this->isMethodAllowed($method)) {
            throw new HttpResponseException(new Response('Method not allowed', 405));
        }

        if (!$this->isAllHeadersAllowed()) {
            $headers = str_replace(' ', '', $request->headers->get('Access-Control-Request-Headers'));

            foreach (explode(',', $headers) as $header) {
                if (!$this->isHeaderAllowed($header)) {
                    throw new HttpResponseException(new Response('Header not allowed', 403));
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
            $response->headers->set('Access-Control-Max-Age', (string)$this->maxAge);
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
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function createResponse(Request $request, Response $response)
    {
        $origin = $request->headers->get('Origin');

        if ($this->isOriginAllowed($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

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
     * Returns whether or not the origin is allowed.
     *
     * @param string|null $origin
     *
     * @return bool
     */
    protected function isOriginAllowed(?string $origin)
    {
        if ($this->isAllOriginsAllowed()) {
            return true;
        }

        return in_array($origin, $this->allowOrigins);
    }


    /**
     * Returns whether or not the method is allowed.
     *
     * @param string|null $method
     *
     * @return bool
     */
    protected function isMethodAllowed(?string $method)
    {
        if ($this->isAllMethodsAllowed()) {
            return true;
        }

        return in_array(strtoupper($method), $this->allowMethods);
    }


    /**
     * Returns whether or not the header is allowed.
     *
     * @param string|null $header
     *
     * @return bool
     */
    protected function isHeaderAllowed(?string $header)
    {
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
     * @param int $maxAge
     */
    protected function setMaxAge(int $maxAge)
    {
        if ($maxAge < 0) {
            throw new \InvalidArgumentException('Max age must be a positive number or zero.');
        }

        $this->maxAge = $maxAge;
    }
}
