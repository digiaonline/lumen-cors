<?php

use Nord\Lumen\Cors\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceTest extends \Codeception\TestCase\Test
{

    use Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;


    protected function _before()
    {
        $this->request  = new Request;
        $this->response = new Response;

        $this->specifyConfig()
            ->shallowClone('request')
            ->shallowClone('response');
    }


    protected function _after()
    {
        $this->request  = null;
        $this->response = null;
    }


    public function testHandlePreflightRequest()
    {
        $this->specify('403 response if origin is not allowed', function () {
            $service = new CorsService;

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
        });

        $this->specify('405 response if method is not allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(405);
        });

        $this->specify('403 response if header is not allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
        });

        $this->specify('200 response when origin, method and headers are allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
                'allowHeaders' => ['accept', 'authorization', 'content-type'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(200);
        });

        $this->specify('response headers are set', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
                'allowHeaders' => ['accept', 'authorization', 'content-type'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
            verify($response->headers->get('Access-Control-Allow-Methods'))->equals('POST');
            verify($response->headers->get('Access-Control-Allow-Headers'))->equals('accept, authorization, content-type');
            verify($response->headers->has('Access-Control-Allow-Credentials'))->false();
            verify($response->headers->has('Access-Control-Max-Age'))->false();
        });

        $this->specify('response credentials header is set', function () {
            $service = new CorsService([
                'allowOrigins'     => ['*'],
                'allowMethods'     => ['*'],
                'allowHeaders'     => ['*'],
                'allowCredentials' => true,
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->specify('response max-age header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
                'allowMethods' => ['*'],
                'allowHeaders' => ['*'],
                'maxAge'       => 3600,
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Max-Age'))->equals(3600);
        });
    }


    public function testHandleRequest()
    {
        $this->specify('response origin header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
        });

        $this->specify('response vary header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Vary', 'Accept-Encoding');

            $response = $service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Vary'))->equals('Accept-Encoding, Origin');
        });

        $this->specify('response credentials header is set', function () {
            $service = new CorsService([
                'allowOrigins'     => ['*'],
                'allowMethods'     => ['*'],
                'allowHeaders'     => ['*'],
                'allowCredentials' => true,
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->specify('response expose headers header is set', function () {
            $service = new CorsService([
                'allowOrigins'  => ['*'],
                'allowMethods'  => ['*'],
                'allowHeaders'  => ['*'],
                'exposeHeaders' => ['Accept', 'Authorization', 'Content-Type'],
            ]);

            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Expose-Headers'))->equals('accept, authorization, content-type');
        });
    }


    public function testIsCorsRequest()
    {
        $this->specify('cors request is recognized', function () {
            $service = new CorsService;

            verify($service->isCorsRequest($this->request))->false();

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($service->isCorsRequest($this->request))->true();
        });
    }


    public function testIsPreflightRequest()
    {
        $this->specify('preflight request is recognized', function () {
            $service = new CorsService;

            verify($service->isPreflightRequest($this->request))->false();

            $this->request->setMethod('OPTIONS');

            verify($service->isPreflightRequest($this->request))->false();

            $this->request->headers->set('Access-Control-Request-Method', 'POST');

            verify($service->isPreflightRequest($this->request))->false();

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($service->isPreflightRequest($this->request))->true();
        });
    }


    public function testIsRequestAllowed()
    {
        $this->specify('request is allowed', function () {
            $service = new CorsService;

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($service->isRequestAllowed($this->request))->false();

            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
            ]);

            verify($service->isRequestAllowed($this->request))->true();
        });
    }

}
