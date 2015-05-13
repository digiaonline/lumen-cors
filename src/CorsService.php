<?php namespace Nord\Lumen\Cors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;

class CorsService implements CorsServiceContract
{

    /**
     * @var array
     */
    private $allowOrigins;

    /**
     * @var array
     */
    private $allowHeaders;

    /**
     * @var array
     */
    private $allowMethods;

    /**
     * @var bool
     */
    private $allowCredentials;

    /**
     * @var array
     */
    private $exposeHeaders;

    /**
     * @var int
     */
    private $maxAge;


    /**
     * CorsService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [ ])
    {
        $this->configure($config);
    }


    /**
     * @param array $config
     */
    private function configure(array $config)
    {
        $this->allowOrigins     = array_get($config, 'allowOrigins', [ ]);
        $this->allowHeaders     = array_get($config, 'allowHeaders', [ ]);
        $this->allowMethods     = array_get($config, 'allowMethods', [ ]);
        $this->allowCredentials = array_get($config, 'allowCredentials', false);
        $this->exposeHeaders    = array_get($config, 'exposeHeaders', [ ]);
        $this->maxAge           = array_get($config, 'maxAge', 0);

        if (in_array('*', $this->allowOrigins)) {
            $this->allowOrigins = true;
        }

        if (in_array('*', array_map('strtoupper', $this->allowHeaders))) {
            $this->allowHeaders = true;
        }

        if (in_array('*', array_map('strtoupper', $this->allowMethods))) {
            $this->allowMethods = true;
        }
    }


    /**
     * @inheritdoc
     */
    public function handlePreflightRequest(Request $request)
    {
        $origin = $request->headers->get('Origin');
        if ( ! $this->isOriginAllowed($origin)) {
            return $this->createErrorResponse('Origin not allowed.', 403);
        }

        $method = strtoupper($request->headers->get('Access-Control-Request-Method'));
        if ( ! $this->isMethodAllowed($method)) {
            return $this->createErrorResponse('Method not allowed.', 403);
        }

        if ( ! $this->allowHeaders && $request->headers->has('Access-Control-Request-Headers')) {
            $headers = $request->headers->get('Access-Control-Request-Headers');
            foreach (explode(', ', $headers) as $header) {
                if ( ! $this->isHeaderAllowed($header)) {
                    return $this->createErrorResponse('Header not allowed.', 403);
                }
            }
        }

        return $this->createPreflightResponse($request);
    }


    /**
     * @inheritdoc
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

        $allowMethods = $this->allowMethods === true
            ? strtoupper($request->headers->get('Access-Control-Request-Method'))
            : implode(', ', $this->allowMethods);

        $response->headers->set('Access-Control-Allow-Methods', $allowMethods);

        $allowHeaders = $this->allowHeaders === true
            ? strtoupper($request->headers->get('Access-Control-Request-Headers'))
            : implode(', ', $this->allowHeaders);

        $response->headers->set('Access-Control-Allow-Headers', $allowHeaders);

        return $response;
    }


    /**
     * @inheritdoc
     */
    public function handleRequest(Request $request, Response $response)
    {
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        $vary = $response->headers->has('Vary') ? $response->headers->get('Vary') . ', Origin' : 'Origin';
        $response->headers->set('Vary', $vary);

        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->exposeHeaders) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposeHeaders));
        }

        return $response;
    }


    /**
     * @param string $content
     * @param int    $status
     *
     * @return \Illuminate\Http\Response
     */
    protected function createErrorResponse($content = '', $status = 400)
    {
        return new Response($content, $status);
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
     * @inheritdoc
     */
    public function isRequestAllowed(Request $request)
    {
        return $this->isOriginAllowed($request->headers->get('Origin'));
    }


    /**
     * @param string $origin
     *
     * @return bool
     */
    protected function isOriginAllowed($origin)
    {
        return $this->allowOrigins === true ?: in_array($origin, $this->allowOrigins);
    }


    /**
     * @param string $method
     *
     * @return bool
     */
    protected function isMethodAllowed($method)
    {
        return $this->allowMethods === true ?: in_array(strtoupper($method), $this->allowMethods);
    }


    /**
     * @param string $header
     *
     * @return bool
     */
    protected function isHeaderAllowed($header)
    {
        return $this->allowHeaders === true ?: in_array(strtoupper($header), $this->allowHeaders);
    }
}
