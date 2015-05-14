<?php namespace Nord\Lumen\Cors;

use Illuminate\Support\ServiceProvider;

class CorsServiceProvider extends ServiceProvider
{

    /**
     * @inheritdoc
     */
    public function register()
    {
        $config = $this->app['config']['cors'];

        $this->app->bind('Nord\Lumen\Cors\CorsService', function () use ($config) {
            return new CorsService($config);
        });

        $this->app->alias('Nord\Lumen\Cors\CorsService', 'Nord\Lumen\Cors\Contracts\CorsService');

        class_alias('Nord\Lumen\Cors\CorsFacade', 'Cors');
    }
}
