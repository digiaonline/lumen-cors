<?php namespace Nord\Lumen\Cors;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;

class CorsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerBindings();
        $this->registerFacades();
    }


    /**
     * Registers container bindings.
     */
    protected function registerBindings()
    {
        $this->app->bind(CorsServiceContract::class, function () {
            return new CorsService(config('cors'));
        });
    }


    /**
     * Registers facades.
     */
    protected function registerFacades()
    {
        if (!class_exists('Cors')) {
            class_alias(CorsFacade::class, 'Cors');
        }
    }
}
