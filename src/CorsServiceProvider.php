<?php namespace Nord\Lumen\Cors;

use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;
use Illuminate\Support\ServiceProvider;

class CorsServiceProvider extends ServiceProvider
{

    /**
     * @inheritdoc
     */
    public function register()
    {
        $config = $this->app['config']['cors'];

        $this->app->bind(CorsService::class, function () use ($config) {
            return new CorsService($config);
        });

        $this->app->alias(CorsService::class, CorsServiceContract::class);

        class_alias(CorsFacade::class, 'Cors');
    }
}
