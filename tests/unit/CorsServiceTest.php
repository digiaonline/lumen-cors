<?php

namespace Nord\Lumen\Cors\Tests;

use Closure;
use Nord\Lumen\Cors\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;
    use \Codeception\AssertThrows;

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

    /**
     * @var Closure
     */
    protected $closure;

    public function testServiceConfig()
    {
        // service config max_age is less than zero
        $this->assertThrows(\InvalidArgumentException::class, function () {
            new CorsService(['max_age' => -1]);
        });
    }

    public function testHandlePreflightRequest()
    {
        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
            'allow_methods' => ['post'],
            'allow_headers' => ['accept', 'authorization', 'content-type'],
        ]);

        $this->request = new Request;

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

        $this->request = new Request;

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
            'allow_origins' => ['http://foo.com'],
            'allow_methods' => ['post'],
            'allow_headers' => ['accept', 'authorization', 'content-type'],
        ]);

        $this->request = new Request;

        $this->specify('regression test for issue #31', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept,authorization, content-type');

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

        $this->request = new Request;

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

        $this->request = new Request;

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
        $this->request = new Request;

        $this->response = new Response;

        $this->service = new CorsService([
            'allow_origins' => ['*'],
        ]);

        $this->specify('response origin header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, new Response());

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('*');
        });

        $this->service = new CorsService([
            'allow_origins' => ['*'],
        ]);

        $this->specify('response vary header is not set when all origins are allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = new Response();
            $response->headers->set('Vary', 'Accept-Encoding');
            $response = $this->service->handleRequest($this->request, $response);

            verify($response->headers->get('Vary'))->equals('Accept-Encoding');
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
        ]);

        $this->specify('response vary header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Vary', 'Accept-Encoding');

            $response = $this->service->handleRequest($this->request, new Response());

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

            $response = $this->service->handleRequest($this->request, new Response());

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

            $response = $this->service->handleRequest($this->request, new Response());

            verify($response->headers->get('Access-Control-Expose-Headers'))->equals('accept, authorization, content-type');
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com', 'http://notbar.com'],
        ]);

        $this->specify('response origin header is not set when origin is not allowed', function () {
            $this->request->headers->set('Origin', 'http://bar.com');

            $response = $this->service->handleRequest($this->request, new Response());

            verify($response->getStatusCode())->equals(200);
            verify($response->headers->get('Access-Control-Allow-Origin'))->equals(null);
        });
    }

    public function testIsCorsRequest()
    {
        $this->service = new CorsService;

        $this->request = new Request;

        $this->specify('cors request is recognized', function () {
            verify($this->service->isCorsRequest($this->request))->false();

            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isCorsRequest($this->request))->true();
        });
    }

    public function testIsPreflightRequest()
    {
        $this->service = new CorsService;

        $this->request = new Request;

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

    public function testAllowOriginIfMatchPattern()
    {
        $this->request = new Request;

        $this->response = new Response;

        $this->service = new CorsService([
            'allow_origins' => ['http://*.foo.com', 'http://notbar.com'],
        ]);

        $this->specify('response origin header is set when origin is match to a pattern', function () {
            $this->request->headers->set('Origin', 'http://bar.foo.com');

            $response = $this->service->handleRequest($this->request, new Response());

            verify($response->getStatusCode())->equals(200);
            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://bar.foo.com');
        });
    }

    public function testDenyOriginIfDoesNotMatchPattern()
    {
        $this->request = new Request;

        $this->response = new Response;

        $this->service = new CorsService([
            'allow_origins' => ['http://*.foo.com', 'http://notbar.com'],
        ]);

        $this->specify('response origin header is not set when origin is not a match to a pattern', function () {
            $this->request->headers->set('Origin', 'http://bar.com');

            $response = $this->service->handleRequest($this->request, new Response());

            verify($response->getStatusCode())->equals(200);
            verify($response->headers->get('Access-Control-Allow-Origin'))->equals(null);
        });
    }
}
