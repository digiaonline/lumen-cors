<?php namespace Nord\Lumen\Cors;

use Illuminate\Support\ServiceProvider;
use Nord\Lumen\Cors\Contracts\CorsService as CorsServiceContract;

class CorsServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'cors';

    /**
     * @inheritdoc
     */
    public function register()
    {
        // In Lumen application configuration files needs to be loaded implicitly
        if ($this->app instanceof \Laravel\Lumen\Application) {
            $this->app->configure(self::CONFIG_KEY);
        } else {
            $this->publishes([$this->configPath() => config_path('cors.php')]);
        }

        $this->registerBindings();
        $this->registerFacades();
    }


    /**
     * Registers container bindings.
     */
    protected function registerBindings()
    {
        // TODO: Change to bind the implementation to the interface instead.
        $this->app->bind(CorsService::class, function () {
            return new CorsService(config(self::CONFIG_KEY));
        });

        $this->app->alias(CorsService::class, CorsServiceContract::class);
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

    /**
     * Default config file path
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/../config/cors.php';
    }
}
