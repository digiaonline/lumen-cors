<?php

namespace Nord\Lumen\Cors\Tests;

use Helper\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nord\Lumen\Cors\CorsService;
use Nord\Lumen\Cors\CorsServiceProvider;
use Nord\Lumen\Cors\CorsMiddleware;

class CorsMiddlewareTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;
    /**
     * @var CorsMiddleware
     */
    private $middleware;

    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $app = new MockApplication();
        $app->register(CorsServiceProvider::class);

        $this->middleware = new CorsMiddleware($app->make(CorsService::class));
    }

    /**
     *
     */
    public function testAssertIsNotCorsRequest()
    {
        $this->specify('verify middleware is not cors request', function () {
            verify($this->middleware->handle(new Request(), function () {
                return true;
            }))->equals(true);
        });
    }

    /**
     *
     */
    public function testAssertIsCorsRequest()
    {
        $this->specify('verify middleware is cors request', function () {
            $req = new Request();
            $req->headers->set('Origin', 'http://example.com');
            $res = $this->middleware->handle($req, function () {
                return new JsonResponse();
            });

            verify($res)->hasAttribute('headers');
            verify($res->headers->get('Access-Control-Allow-Origin'))->equals('http://example.com');
        });

        $service = new CorsService([
            'allow_origins' => ['http://foo.com'],
        ]);
        $this->middleware = new CorsMiddleware($service);
        $this->specify('Closure not called when origin is not allowed', function () {
            $req = new Request();
            $req->headers->set('Origin', 'http://bar.com');
            $res = $this->middleware->handle($req, function () {
                $res = new JsonResponse();
                $res->headers->set('X-Closure-Called', 1);
                return $res;
            });
            verify($res)->hasAttribute('headers');
            verify($res->headers->get('X-Closure-Called'))->equals(null);
        });
    }

    /**
     *
     */
    public function testAssertIsPreflightRequest()
    {
        $this->specify('verify middleware is preflight request', function () {
            $req = new Request();
            $req->setMethod('OPTIONS');
            $req->headers->set('Origin', 'http://example.com');
            $req->headers->set('Access-Control-Request-Method', 'GET');

            $res = $this->middleware->handle($req, function () {
                return new JsonResponse();
            });

            verify($res)->hasAttribute('headers');
            verify($res->headers->get('Access-Control-Allow-Origin'))->equals('http://example.com');
            verify($res->headers->get('Access-Control-Allow-Methods'))->equals('GET');
        });
    }
}
