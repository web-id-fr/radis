<?php

namespace WebId\Radis\Classes;

class ForgeFormatter
{
    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public static function getFeatureDatabase(string $siteName, string $databaseName = null): string
    {
        return $databaseName ?? $siteName . 'db';
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public static function getFeatureDatabaseUser(string $siteName, string $databaseName = null): string
    {
        return self::getFeatureDatabase($siteName, $databaseName) . 'user';
    }

    /**
     * @param string $siteName
     * @return string
     */
    public static function getFeatureDomain(string $siteName): string
    {
        return $siteName . '-feature.' . config('radis.forge.server_domain');
    }

    /**
     * @return string
     */
    public static function getFeatureDatabasePassword(): string
    {
        return config('radis.forge.database_password');
    }
}
