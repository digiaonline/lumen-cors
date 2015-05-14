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
     * @var CorsService
     */
    protected $service;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;


    public function testHandlePreflightRequest()
    {
        $this->service = new CorsService;

        $this->request = new Request;

        $this->specify('403 response if origin is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
        });

        $this->service = new CorsService([
            'allowOrigins' => ['http://foo.com'],
        ]);

        $this->specify('405 response if method is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(405);
        });

        $this->service = new CorsService([
            'allowOrigins' => ['http://foo.com'],
            'allowMethods' => ['post'],
        ]);

        $this->specify('403 response if header is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
        });

        $this->service = new CorsService([
            'allowOrigins' => ['http://foo.com'],
            'allowMethods' => ['post'],
            'allowHeaders' => ['accept', 'authorization', 'content-type'],
        ]);

        $this->specify('200 response when origin, method and headers are allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(200);
        });

        $this->service = new CorsService([
            'allowOrigins' => ['http://foo.com'],
            'allowMethods' => ['post'],
            'allowHeaders' => ['accept', 'authorization', 'content-type'],
        ]);

        $this->specify('response headers are set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
            verify($response->headers->get('Access-Control-Allow-Methods'))->equals('POST');
            verify($response->headers->get('Access-Control-Allow-Headers'))->equals('accept, authorization, content-type');
            verify($response->headers->has('Access-Control-Allow-Credentials'))->false();
            verify($response->headers->has('Access-Control-Max-Age'))->false();
        });

        $this->service = new CorsService([
            'allowOrigins'     => ['*'],
            'allowMethods'     => ['*'],
            'allowHeaders'     => ['*'],
            'allowCredentials' => true,
        ]);

        $this->specify('response credentials header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->service = new CorsService([
            'allowOrigins' => ['*'],
            'allowMethods' => ['*'],
            'allowHeaders' => ['*'],
            'maxAge'       => 3600,
        ]);

        $this->specify('response max-age header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Max-Age'))->equals(3600);
        });
    }


    public function testHandleRequest()
    {
        $this->request  = new Request;

        $this->response = new Response;

        $this->service = new CorsService([
            'allowOrigins' => ['*'],
        ]);

        $this->specify('response origin header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
        });

        $this->service = new CorsService([
            'allowOrigins' => ['*'],
        ]);

        $this->specify('response vary header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Vary', 'Accept-Encoding');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Vary'))->equals('Accept-Encoding, Origin');
        });

        $this->service = new CorsService([
            'allowOrigins'     => ['*'],
            'allowMethods'     => ['*'],
            'allowHeaders'     => ['*'],
            'allowCredentials' => true,
        ]);

        $this->specify('response credentials header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->service = new CorsService([
            'allowOrigins'  => ['*'],
            'allowMethods'  => ['*'],
            'allowHeaders'  => ['*'],
            'exposeHeaders' => ['Accept', 'Authorization', 'Content-Type'],
        ]);

        $this->specify('response expose headers header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Expose-Headers'))->equals('accept, authorization, content-type');
        });
    }


    public function testIsCorsRequest()
    {
        $this->service = new CorsService;

        $this->request  = new Request;

        $this->specify('cors request is recognized', function () {
            verify($this->service->isCorsRequest($this->request))->false();

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isCorsRequest($this->request))->true();
        });
    }


    public function testIsPreflightRequest()
    {
        $this->service = new CorsService;

        $this->request  = new Request;

        $this->specify('preflight request is recognized', function () {
            verify($this->service->isPreflightRequest($this->request))->false();

            $this->request->setMethod('OPTIONS');

            verify($this->service->isPreflightRequest($this->request))->false();

            $this->request->headers->set('Access-Control-Request-Method', 'POST');

            verify($this->service->isPreflightRequest($this->request))->false();

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isPreflightRequest($this->request))->true();
        });
    }


    public function testIsRequestAllowed()
    {
        $this->service = new CorsService;

        $this->request  = new Request;

        $this->specify('request is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isRequestAllowed($this->request))->false();
        });

        $this->service = new CorsService([
            'allowOrigins' => ['http://foo.com'],
        ]);

        $this->specify('request is allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isRequestAllowed($this->request))->true();
        });
    }

}
