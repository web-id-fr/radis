<?php

namespace WebId\Radis;

use Illuminate\Support\ServiceProvider;
use WebId\Radis\Console\Commands\DeployCommand;

class RadisProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployCommand::class,
            ]);
        }
    }
}
