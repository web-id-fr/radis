<?php

namespace WebId\Radis\Console\Commands\Traits;

trait TranslateSiteName
{
    /**
     * This method replaces invalid characters with a dash.
     */
    protected function translateSiteName(string $siteName): string
    {
        $siteName = preg_replace("/[^\w_]+/i", '-', $siteName);

        return $siteName;
    }
}
