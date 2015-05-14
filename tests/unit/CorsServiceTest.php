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


    public function testHandlePreflightRequest()
    {
        $this->specify('403 response if origin is not allowed', function () {
            $service = new CorsService();

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals(403, $response->getStatusCode());
        });

        $this->specify('405 response if method is not allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals(405, $response->getStatusCode());
        });

        $this->specify('403 response if header is not allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals(403, $response->getStatusCode());
        });

        $this->specify('200 response when origin, method and headers are allowed', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
                'allowHeaders' => ['accept', 'authorization', 'content-type'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals(200, $response->getStatusCode());
        });

        $this->specify('response headers are set', function () {
            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
                'allowMethods' => ['post'],
                'allowHeaders' => ['accept', 'authorization', 'content-type'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals('http://foo.com', $response->headers->get('Access-Control-Allow-Origin'));
            $this->assertEquals('POST', $response->headers->get('Access-Control-Allow-Methods'));
            $this->assertEquals('accept, authorization, content-type',
                $response->headers->get('Access-Control-Allow-Headers'));
            $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
            $this->assertFalse($response->headers->has('Access-Control-Max-Age'));
        });

        $this->specify('response credentials header is set', function () {
            $service = new CorsService([
                'allowOrigins'     => ['*'],
                'allowMethods'     => ['*'],
                'allowHeaders'     => ['*'],
                'allowCredentials' => true,
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        });

        $this->specify('response max-age header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
                'allowMethods' => ['*'],
                'allowHeaders' => ['*'],
                'maxAge'       => 3600,
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Access-Control-Request-Method', 'POST');
            $request->headers->set('Access-Control-Request-Headers', 'accept, authorization, content-type');

            $response = $service->handlePreflightRequest($request);

            $this->assertEquals(3600, $response->headers->get('Access-Control-Max-Age'));
        });
    }


    public function testHandleRequest()
    {
        $this->specify('response origin header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');

            $response = new Response();

            $response = $service->handleRequest($request, $response);

            $this->assertEquals('http://foo.com', $response->headers->get('Access-Control-Allow-Origin'));
        });

        $this->specify('response vary header is set', function () {
            $service = new CorsService([
                'allowOrigins' => ['*'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');
            $request->headers->set('Vary', 'Accept-Encoding');

            $response = new Response();

            $response = $service->handleRequest($request, $response);
            $this->assertEquals('Accept-Encoding, Origin', $response->headers->get('Vary'));
        });

        $this->specify('response credentials header is set', function () {
            $service = new CorsService([
                'allowOrigins'     => ['*'],
                'allowMethods'     => ['*'],
                'allowHeaders'     => ['*'],
                'allowCredentials' => true,
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');

            $response = new Response();

            $response = $service->handleRequest($request, $response);

            $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        });

        $this->specify('response expose headers header is set', function () {
            $service = new CorsService([
                'allowOrigins'  => ['*'],
                'allowMethods'  => ['*'],
                'allowHeaders'  => ['*'],
                'exposeHeaders' => ['Accept', 'Authorization', 'Content-Type'],
            ]);

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');

            $response = new Response();

            $response = $service->handleRequest($request, $response);

            $this->assertEquals('accept, authorization, content-type',
                $response->headers->get('Access-Control-Expose-Headers'));
        });
    }


    public function testIsCorsRequest()
    {
        $this->specify('cors request is recognized', function () {
            $service = new CorsService();

            $request = new Request();

            $this->assertFalse($service->isCorsRequest($request));

            $request->headers->set('Origin', 'http://foo.com');
            $this->assertTrue($service->isCorsRequest($request));
        });
    }


    public function testIsPreflightRequest()
    {
        $this->specify('preflight request is recognized', function () {
            $service = new CorsService();

            $request = new Request();

            $this->assertFalse($service->isPreflightRequest($request));

            $request->setMethod('OPTIONS');
            $this->assertFalse($service->isPreflightRequest($request));

            $request->headers->set('Access-Control-Request-Method', 'POST');
            $this->assertFalse($service->isPreflightRequest($request));

            $request->headers->set('Origin', 'http://foo.com');
            $this->assertTrue($service->isPreflightRequest($request));
        });
    }


    public function testIsRequestAllowed()
    {
        $this->specify('request is allowed', function () {

            $service = new CorsService();

            $request = new Request();
            $request->headers->set('Origin', 'http://foo.com');

            $this->assertFalse($service->isRequestAllowed($request));

            $service = new CorsService([
                'allowOrigins' => ['http://foo.com'],
            ]);

            $this->assertTrue($service->isRequestAllowed($request));
        });
    }

}
