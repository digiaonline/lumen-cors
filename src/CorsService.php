<?php namespace Nord\Lumen\Cors;

use Illuminate\Support\Str;
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
        $response = new Response();

        // Do not set any headers if the origin is not allowed
        if ($this->isOriginAllowed($request->headers->get('Origin'))) {
            $response = $this->setAccessControlAllowOriginHeader($request, $response);

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
        }

        return $response;
    }


    /**
     * @inheritdoc
     */
    public function handleRequest(Request $request, Response $response): Response
    {
        // Do not set any headers if the origin is not allowed
        if ($this->isOriginAllowed($request->headers->get('Origin'))) {
            $response = $this->setAccessControlAllowOriginHeader($request, $response);

            // Set Vary unless all origins are allowed
            if (!$this->isAllOriginsAllowed()) {
                $vary = $request->headers->has('Vary') ? $request->headers->get('Vary') . ', Origin' : 'Origin';
                $response->headers->set('Vary', $vary);
            }

            if ($this->allowCredentials) {
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }

            if (!empty($this->exposeHeaders)) {
                $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposeHeaders));
            }
        }

        return $response;
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
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function setAccessControlAllowOriginHeader(Request $request, Response $response): Response
    {
        $origin = $request->headers->get('Origin');

        if ($this->isAllOriginsAllowed()) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif ($this->isOriginAllowed($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
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

        return Str::is($this->allowOrigins, $origin);
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
     *
     * @return self
     */
    protected function setAllowMethods(array $allowMethods): self
    {
        $this->allowMethods = array_map('strtoupper', $allowMethods);
        
        return $this;
    }


    /**
     * @param array $allowHeaders
     *
     * @return self
     */
    protected function setAllowHeaders(array $allowHeaders): self
    {
        $this->allowHeaders = array_map('strtolower', $allowHeaders);
        
        return $this;
    }


    /**
     * @param array $exposeHeaders
     *
     * @return self
     */
    protected function setExposeHeaders(array $exposeHeaders): self
    {
        $this->exposeHeaders = array_map('strtolower', $exposeHeaders);
        
        return $this;
    }


    /**
     * @param int $maxAge
     *
     * @return self
     */
    protected function setMaxAge(int $maxAge): self
    {
        if ($maxAge < 0) {
            throw new \InvalidArgumentException('Max age must be a positive number or zero.');
        }

        $this->maxAge = $maxAge;
        
        return $this;
    }
}
