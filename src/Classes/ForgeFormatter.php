<?php

namespace WebId\Radis\Classes;

use Illuminate\Support\Facades\Config;

class ForgeFormatter
{
    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public static function getFeatureDatabase(string $siteName, string $databaseName = null): string
    {
        $databaseName = $databaseName ?? $siteName . 'db';

        // Removing invalid '-' character from the database name
        $databaseName = str_replace('-', '_', $databaseName);

        return $databaseName;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public static function getFeatureDatabaseUser(string $siteName, string $databaseName = null): string
    {
        // new limit on forge: 16 chars max !
        // else, validation errors in Laravel Forge
        // 'RA' for review app to prevent conflicts with other users
        return substr('RA' . self::getFeatureDatabase($siteName, $databaseName), 0, 14);
    }

    /**
     * @param string $siteName
     * @return string
     */
    public static function getFeatureDomain(string $siteName): string
    {
        $domain = $siteName . '.' . Config::get('radis.forge.server_domain');

        // Removing unallowed characters in domain name
        $domain = str_replace('_', '-', $domain);

        return $domain;
    }

    /**
     * @return string
     */
    public static function getFeatureDatabasePassword(): string
    {
        return Config::get('radis.forge.database_password');
    }
}
