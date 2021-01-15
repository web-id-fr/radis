<?php

namespace WebId\Radis;

use Illuminate\Support\ServiceProvider;
use WebId\Radis\Console\Commands\DeployCommand;
use WebId\Radis\Console\Commands\DestroyCommand;
use WebId\Radis\Services\ForgeService;

class RadisProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/radis.php', 'radis');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
            $this->commands([
                DeployCommand::class,
                DestroyCommand::class,
            ]);
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/radis.php' => config_path('radis.php'),
        ], 'radis.config');
    }
}
