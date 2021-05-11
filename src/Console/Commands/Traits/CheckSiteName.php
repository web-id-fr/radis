<?php

namespace WebId\Radis\Console\Commands\Traits;

trait CheckSiteName
{
    protected function checkSiteName(string $siteName): bool
    {
        $isSiteNameValid = boolval(preg_match("/^[\w_]+$/i", $siteName));

        if (! $isSiteNameValid) {
            $this->error("The site name '{$siteName}' is invalid, please only use letters and underscores.");
        }

        return $isSiteNameValid;
    }
}
