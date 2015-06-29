<?php namespace Nord\Lumen\Cors;

use Illuminate\Support\ServiceProvider;

class CorsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;


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

        if (!static::$aliasesRegistered) {
            static::$aliasesRegistered = true;
            class_alias('Nord\Lumen\Cors\CorsFacade', 'Cors');
        }
    }
}
