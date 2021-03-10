<?php

namespace WebId\Radis\Console\Commands\Traits;

use Illuminate\Support\Facades\Config;

trait CheckConfig
{
    /**
     * @param string $key
     */
    protected function checkConfig(string $key): void
    {
        if (empty(Config::get($key))) {
            throw new \RuntimeException(sprintf(
                'Config key "%s" was not found.',
                $key
            ));
        }
    }
}
