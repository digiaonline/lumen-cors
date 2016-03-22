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
            'allow_origins' => ['http://foo.com'],
        ]);

        $this->specify('405 response if method is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(405);
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
            'allow_methods' => ['post'],
        ]);

        $this->specify('403 response if header is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
            'allow_methods' => ['post'],
            'allow_headers' => ['accept', 'authorization', 'content-type'],
        ]);

        $this->specify('200 response when origin, method and headers are allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(200);
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
            'allow_methods' => ['post'],
            'allow_headers' => ['accept', 'authorization', 'content-type'],
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
            'allow_origins'     => ['*'],
            'allow_methods'     => ['*'],
            'allow_headers'     => ['*'],
            'allow_credentials' => true,
        ]);

        $this->specify('response credentials header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->service = new CorsService([
            'allow_origins' => ['*'],
            'allow_methods' => ['*'],
            'allow_headers' => ['*'],
            'max_age'       => 3600,
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
            'allow_origins' => ['*'],
        ]);

        $this->specify('response origin header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
        });

        $this->service = new CorsService([
            'allow_origins' => ['*'],
        ]);

        $this->specify('response vary header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Vary', 'Accept-Encoding');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Vary'))->equals('Accept-Encoding, Origin');
        });

        $this->service = new CorsService([
            'allow_origins'     => ['*'],
            'allow_methods'     => ['*'],
            'allow_headers'     => ['*'],
            'allow_credentials' => true,
        ]);

        $this->specify('response credentials header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Allow-Credentials'))->equals('true');
        });

        $this->service = new CorsService([
            'allow_origins'  => ['*'],
            'allow_methods'  => ['*'],
            'allow_headers'  => ['*'],
            'expose_headers' => ['Accept', 'Authorization', 'Content-Type'],
        ]);

        $this->specify('response expose headers header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->response);

            verify($response->headers->get('Access-Control-Expose-Headers'))->equals('accept, authorization, content-type');
        });
    }


    public function testCreateOriginNotAllowedResponse()
    {
        $this->request  = new Request;

        $this->response = new Response;

        $this->service = new CorsService();

        $this->specify('default origin not allowed response is created', function () {
            $response = $this->service->createOriginNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Origin not allowed.');
            verify($response->getStatusCode())->equals(403);
        });

        $this->service = new CorsService([
            'origin_not_allowed' => function ($request) {
              return new Response('Foo', 403);
            },
        ]);

        $this->specify('custom origin not allowed response is created', function () {
            $response = $this->service->createOriginNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Foo');
            verify($response->getStatusCode())->equals(403);
        });
    }


    public function testCreateMethodNotAllowedResponse()
    {
        $this->request  = new Request;

        $this->response = new Response;

        $this->service = new CorsService();

        $this->specify('default method not allowed response is created', function () {
            $response = $this->service->createMethodNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Method not allowed.');
            verify($response->getStatusCode())->equals(405);
        });

        $this->service = new CorsService([
            'method_not_allowed' => function ($request) {
              return new Response('Foo', 405);
            },
        ]);

        $this->specify('custom method not allowed response is created', function () {
            $response = $this->service->createMethodNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Foo');
            verify($response->getStatusCode())->equals(405);
        });
    }


    public function testCreateHeaderNotAllowedResponse()
    {
        $this->request  = new Request;

        $this->response = new Response;

        $this->service = new CorsService();

        $this->specify('default header not allowed response is created', function () {
            $response = $this->service->createHeaderNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Header not allowed.');
            verify($response->getStatusCode())->equals(403);
        });

        $this->service = new CorsService([
            'header_not_allowed' => function ($request) {
              return new Response('Foo', 403);
            },
        ]);

        $this->specify('custom header not allowed response is created', function () {
            $response = $this->service->createHeaderNotAllowedResponse($this->request);

            verify($response->getContent())->equals('Foo');
            verify($response->getStatusCode())->equals(403);
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
}
