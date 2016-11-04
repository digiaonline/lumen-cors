<?php

namespace Nord\Lumen\Cors;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;

class CorsServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'cors';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->configure(self::CONFIG_KEY);

        $this->registerBindings($this->app, $this->app['config']);
        $this->registerFacades();
    }

    /**
     * Registers container bindings.
     *
     * @param Container        $container
     * @param ConfigRepository $config
     */
    protected function registerBindings(Container $container, ConfigRepository $config)
    {
        $container->bind(CorsService::class, function () use ($config) {
            return new CorsService($config[self::CONFIG_KEY]);
        });

        $container->alias(CorsService::class, CorsServiceContract::class);
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
