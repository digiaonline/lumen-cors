<?php

namespace Nord\Lumen\Cors\Tests;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nord\Lumen\Cors\CorsMiddleware;
use Nord\Lumen\Cors\CorsService;
use Nord\Lumen\Cors\CorsServiceProvider;

class CorsMiddlewareTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;
    
    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $app = new MockApplication();
        $app->register(CorsServiceProvider::class);
    }

    /**
     *
     */
    public function testAssertIsNotCorsRequest()
    {
        $middleware = new CorsMiddleware(new CorsService());
        $this->specify('verify middleware is not cors request', function () use ($middleware) {
            verify($middleware->handle(new Request(), function () {
                return true;
            }))->equals(true);
        });
    }

    /**
     *
     */
    public function testAssertIsCorsRequest()
    {
        $service = new CorsService([
            'allow_origins' => ['http://example.com'],
        ]);
        $middleware = new CorsMiddleware($service);
        $this->specify('verify middleware is cors request', function () use ($middleware) {
            $req = new Request();
            $req->headers->set('Origin', 'http://example.com');
            $res = $middleware->handle($req, function () {
                return new JsonResponse();
            });

            verify($res)->hasAttribute('headers');
            verify($res->headers->get('Access-Control-Allow-Origin'))->equals('http://example.com');
        });

        $service = new CorsService([
            'allow_origins' => ['http://foo.com'],
        ]);
        $middleware = new CorsMiddleware($service);
        $this->specify('Closure is called even if origin is not allowed', function () use ($middleware) {
            $req = new Request();
            $req->headers->set('Origin', 'http://bar.com');
            $res = $middleware->handle($req, function () {
                $res = new JsonResponse();
                $res->headers->set('X-Closure-Called', 1);
                return $res;
            });
            verify($res)->hasAttribute('headers');
            verify($res->headers->get('X-Closure-Called'))->equals(1);
        });
    }

    /**
     *
     */
    public function testAssertIsPreflightRequest()
    {
        $service = new CorsService([
            'allow_origins' => ['http://example.com'],
            'allow_methods' => ['*'],
        ]);
        $middleware = new CorsMiddleware($service);
        $this->specify('verify middleware is preflight request', function () use ($middleware) {
            $req = new Request();
            $req->setMethod('OPTIONS');
            $req->headers->set('Origin', 'http://example.com');
            $req->headers->set('Access-Control-Request-Method', 'GET');

            $res = $middleware->handle($req, function () {
                return new JsonResponse();
            });

            verify($res)->hasAttribute('headers');
            verify($res->headers->get('Access-Control-Allow-Origin'))->equals('http://example.com');
            verify($res->headers->get('Access-Control-Allow-Methods'))->equals('GET');
        });
    }
}
