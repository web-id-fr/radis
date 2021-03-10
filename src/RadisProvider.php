<?php

namespace WebId\Radis;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use WebId\Radis\Console\Commands\CreateReviewAppCommand;
use WebId\Radis\Console\Commands\DeployScriptCommand;
use WebId\Radis\Console\Commands\DestroyCommand;
use WebId\Radis\Console\Commands\EnvCommand;
use WebId\Radis\Console\Commands\UpdateCommand;
use WebId\Radis\Services\ForgeService;
use WebId\Radis\Services\ForgeServiceContract;
use WebId\Radis\Services\ForgeServiceTesting;

class RadisProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (Config::get('radis.driver') === 'fake') {
            $this->app->bind(ForgeServiceContract::class, ForgeServiceTesting::class);
        } else {
            $this->app->bind(ForgeServiceContract::class, ForgeService::class);
        }

        $this->mergeConfigFrom(__DIR__.'/../config/radis.php', 'radis');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/radis.php' => config_path('radis.php'),
        ], 'config');

        // Publishing stubs.
        $this->publishes([
            __DIR__.'/Stubs/env.stub' => base_path('stubs/env.stub'),
            __DIR__.'/Stubs/deployScript.stub' => base_path('stubs/deployScript.stub'),
        ], 'stub');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        $this->commands([
            CreateReviewAppCommand::class,
            DestroyCommand::class,
            EnvCommand::class,
            DeployScriptCommand::class,
            UpdateCommand::class,
        ]);
    }
}
