<?php

namespace WebId\Radis;

use Illuminate\Support\ServiceProvider;
use WebId\Radis\Console\Commands\CreateReviewAppCommand;
use WebId\Radis\Console\Commands\DeployCommand;
use WebId\Radis\Console\Commands\DeployScriptCommand;
use WebId\Radis\Console\Commands\DestroyCommand;
use WebId\Radis\Console\Commands\EnvCommand;
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
                CreateReviewAppCommand::class,
                DestroyCommand::class,
                EnvCommand::class,
                DeployScriptCommand::class,
                DeployCommand::class,
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
