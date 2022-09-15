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

        // limiting the site name or else there is some
        // validation errors in Laravel Forge
        $siteName = substr($siteName, 0, 20);

        $siteName = trim($siteName, '-');

        return $siteName;
    }
}
