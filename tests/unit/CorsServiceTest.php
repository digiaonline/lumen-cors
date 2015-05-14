<?php

use Nord\Lumen\Cors\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;


    public function testHandlePreflightRequest()
    {
        $service = $this->createService();

        $request = $this->createRequest([
            'Origin'                         => 'http://foo.com',
            'Access-Control-Request-Method'  => 'POST',
            'Access-Control-Request-Headers' => 'accept, authorization, content-type',
        ]);

        $response = $service->handlePreflightRequest($request);
        $this->assertEquals(403, $response->getStatusCode());

        $service = $this->createService([
            'allowOrigins' => ['http://foo.com'],
        ]);

        $response = $service->handlePreflightRequest($request);
        $this->assertEquals(405, $response->getStatusCode());

        $service = $this->createService([
            'allowOrigins' => ['http://foo.com'],
            'allowMethods' => ['post'],
        ]);

        $response = $service->handlePreflightRequest($request);
        $this->assertEquals(403, $response->getStatusCode());

        $service = $this->createService([
            'allowOrigins' => ['http://foo.com'],
            'allowMethods' => ['post'],
            'allowHeaders' => ['accept', 'authorization', 'content-type'],
        ]);

        $response = $service->handlePreflightRequest($request);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('http://foo.com', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('POST', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('accept, authorization, content-type',
            $response->headers->get('Access-Control-Allow-Headers'));
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
        $this->assertFalse($response->headers->has('Access-Control-Max-Age'));

        $service = $this->createService([
            'allowOrigins'     => ['*'],
            'allowMethods'     => ['*'],
            'allowHeaders'     => ['*'],
            'allowCredentials' => true,
            'maxAge'           => 3600,
        ]);

        $response = $service->handlePreflightRequest($request);
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        $this->assertEquals(3600, $response->headers->get('Access-Control-Max-Age'));
    }


    public function testHandleRequest()
    {
        $service = $this->createService([
            'allowOrigins' => ['*'],
        ]);

        $request = $this->createRequest([
            'Origin' => 'http://foo.com',
        ]);

        $response = new Response();

        $response = $service->handleRequest($request, $response);
        $this->assertEquals('http://foo.com', $response->headers->get('Access-Control-Allow-Origin'));

        $request->headers->set('Vary', 'Accept-Encoding');
        $response = $service->handleRequest($request, $response);
        $this->assertEquals('Accept-Encoding, Origin', $response->headers->get('Vary'));

        $service = $this->createService([
            'allowOrigins'     => ['*'],
            'allowMethods'     => ['*'],
            'allowHeaders'     => ['*'],
            'allowCredentials' => true,
            'exposeHeaders'    => ['Accept', 'Authorization', 'Content-Type'],
        ]);

        $response = $service->handleRequest($request, $response);
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        $this->assertEquals('accept, authorization, content-type',
            $response->headers->get('Access-Control-Expose-Headers'));
    }


    public function testIsCorsRequest()
    {
        $service = $this->createService();

        $request = $this->createRequest();

        $this->assertFalse($service->isCorsRequest($request));

        $request->headers->set('Origin', 'http://foo.com');
        $this->assertTrue($service->isCorsRequest($request));
    }


    public function testIsPreflightRequest()
    {
        $service = $this->createService();

        $request = $this->createRequest();

        $this->assertFalse($service->isPreflightRequest($request));

        $request->setMethod('OPTIONS');
        $this->assertFalse($service->isPreflightRequest($request));

        $request->headers->set('Access-Control-Request-Method', 'PUT');
        $this->assertFalse($service->isPreflightRequest($request));

        $request->headers->set('Origin', 'http://foo.com');
        $this->assertTrue($service->isPreflightRequest($request));
    }


    public function testIsRequestAllowed()
    {
        $service = $this->createService();

        $request = $this->createRequest([
            'Origin' => 'http://foo.com',
        ]);

        $this->assertFalse($service->isRequestAllowed($request));

        $service = $this->createService([
            'allowOrigins' => ['http://foo.com'],
        ]);

        $this->assertTrue($service->isRequestAllowed($request));
    }


    /**
     * @param array $config
     *
     * @return CorsService
     */
    private function createService(array $config = [])
    {
        return new CorsService($config);
    }


    /**
     * @param array $headers
     *
     * @return Request
     */
    private function createRequest(array $headers = [])
    {
        $request = new Request();

        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return $request;
    }

}
