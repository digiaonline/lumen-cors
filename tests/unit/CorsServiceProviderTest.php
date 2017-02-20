<?php

namespace Nord\Lumen\Cors\Tests;

use Nord\Lumen\Cors\CorsFacade;
use Nord\Lumen\Cors\CorsService;
use Nord\Lumen\Cors\CorsServiceProvider;

class CorsServiceProviderTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var MockApplication
     */
    protected $app;

    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $this->app = new MockApplication();
        $this->app->withFacades();
        $this->app->register(CorsServiceProvider::class);
    }

    /**
     *
     */
    public function testAssertCanBeRegistered()
    {
        $this->specify('verify serviceProvider is registered', function () {
            $service = $this->app->make(CorsService::class);
            verify($service)->isInstanceOf(CorsService::class);
        });
    }

    /**
     *
     */
    public function testAssertFacade()
    {
        $this->specify('verify serviceProvider facade', function () {
            verify(CorsFacade::getFacadeRoot())->isInstanceOf(CorsService::class);
        });
    }
}
