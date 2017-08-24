<?php

namespace Nord\Lumen\Cors\Tests;

use Closure;
use Nord\Lumen\Cors\CorsService;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

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
        $this->specify('service config allow_credentials is not boolean', function () {
            new CorsService(['allow_credentials' => 'invalid']);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->specify('service config max_age is not integer', function () {
            new CorsService(['max_age' => 'invalid']);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->specify('service config max_age is less than zero', function () {
            new CorsService(['max_age' => -1]);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->specify('service config origin_not_allowed must be callable', function () {
            new CorsService(['origin_not_allowed' => 'INVALID ORIGIN']);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->specify('service config method_not_allowed must be callable', function () {
            new CorsService(['method_not_allowed' => 'INVALID METHOD']);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->specify('service config header_not_allowed must be callable', function () {
            new CorsService(['header_not_allowed' => 'INVALID HEADER']);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);
    }

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

        $this->request = new Request;

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

        $this->request = new Request;

        $this->specify('403 response if header is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $this->service->handlePreflightRequest($this->request);
        });

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
            'allow_origins' => ['*'],
        ]);

        $this->request = new Request;

        $this->specify('InvalidArgument exception when origin is not set', function () {
            $this->service->handlePreflightRequest($this->request);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

        $this->service = new CorsService([
            'allow_origins' => ['*'],
            'allow_headers' => ['accept'],
        ]);

        $this->request = new Request;

        $this->specify('InvalidArgument exception when header is not set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, ');

            $this->service->handlePreflightRequest($this->request);
        }, ['throws' => 'Nord\Lumen\Cors\Exceptions\InvalidArgument']);

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

        $this->service = new CorsService([
            'allow_origins'      => ['http://foo.com'],
            'origin_not_allowed' => function () {
                return new Response('INVALID ORIGIN', 403);
            },
        ]);

        $this->request = new Request;

        $this->specify('response origin_not_allowed header is set', function () {
            $this->request->headers->set('Origin', 'http://bar.com');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
            verify($response->getContent())->equals('INVALID ORIGIN');
        });

        $this->service = new CorsService([
            'allow_origins'      => ['*'],
            'allow_methods'      => ['GET'],
            'method_not_allowed' => function () {
                return new Response('INVALID METHOD', 403);
            },
        ]);

        $this->request = new Request;

        $this->specify('response method_not_allowed header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Method', 'POST');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
            verify($response->getContent())->equals('INVALID METHOD');
        });

        $this->service = new CorsService([
            'allow_origins'      => ['*'],
            'allow_headers'      => ['accept'],
            'header_not_allowed' => function () {
                return new Response('INVALID HEADER', 403);
            },
        ]);

        $this->request = new Request;

        $this->specify('response header_not_allowed header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Access-Control-Request-Headers', 'accept, authorization');

            $response = $this->service->handlePreflightRequest($this->request);

            verify($response->getStatusCode())->equals(403);
            verify($response->getContent())->equals('INVALID HEADER');
        });
    }

    public function testHandleRequest()
    {
        $this->request = new Request;

        $this->response = new Response;

        $this->closure = function () {
            return new Response;
        };

        $this->service = new CorsService([
            'allow_origins' => ['*'],
        ]);

        $this->specify('response origin header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            $response = $this->service->handleRequest($this->request, $this->closure);

            verify($response->headers->get('Access-Control-Allow-Origin'))->equals('http://foo.com');
        });

        $this->service = new CorsService([
            'allow_origins' => ['*'],
        ]);

        $this->specify('response vary header is set', function () {
            $this->request->headers->set('Origin', 'http://foo.com');
            $this->request->headers->set('Vary', 'Accept-Encoding');

            $response = $this->service->handleRequest($this->request, $this->closure);

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

            $response = $this->service->handleRequest($this->request, $this->closure);

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

            $response = $this->service->handleRequest($this->request, $this->closure);

            verify($response->headers->get('Access-Control-Expose-Headers'))->equals('accept, authorization, content-type');
        });

        $this->service = new CorsService([
            'allow_origins' => ['http://foo.com'],
        ]);

        $this->specify('403 response when origin is not allowed', function () {
            $this->request->headers->set('Origin', 'http://bar.com');

            $response = $this->service->handleRequest($this->request, $this->closure);

            verify($response->getStatusCode())->equals(403);
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
}
