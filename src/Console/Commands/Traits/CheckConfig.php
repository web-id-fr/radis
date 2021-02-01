<?php

namespace WebId\Radis\Console\Commands\Traits;

trait CheckConfig
{
    /**
     * @param string $key
     */
    protected function checkConfig(string $key): void
    {
        if (empty(config($key))) {
            throw new \RuntimeException(sprintf(
                'Config key "%s" was not found.',
                $key
            ));
        }
    }
}
