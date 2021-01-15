<?php

namespace WebId\Radis\Console\Commands\Traits;

trait CheckConfig
{
    /**
     * @param $key
     */
    private function checkConfig($key)
    {
        if (empty(config($key))) {
            throw new \RuntimeException(sprintf(
                'Config key "%s" was not found.',
                $key
            ));
        }
    }
}
