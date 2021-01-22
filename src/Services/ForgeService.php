<?php

namespace WebId\Radis\Services;

use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Database;
use Laravel\Forge\Resources\DatabaseUser;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use WebId\Radis\Classes\ForgeFormatter;

class ForgeService implements ForgeServiceContract
{
    /** @var Forge  */
    private $forge;

    public function __construct()
    {
        $this->forge = new Forge(config('radis.forge.token'));
    }

    /**
     * @return Server
     */
    public function getForgeServer(): Server
    {
        $forgeServerName = config('radis.forge.server_name');

        foreach ($this->forge->servers() as $server) {
            if ($server->name === $forgeServerName) {
                return $server;
            }
        }

        throw new \RuntimeException(sprintf(
            'Forge server with name "%s" was not found.',
            $forgeServerName
        ));
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @return bool
     */
    public function deleteForgeSiteIfExists(Server $forgeServer, string $siteName): bool
    {
        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);
        foreach ($this->forge->sites($forgeServer->id) as $site) {
            if ($site->name === $featureDomain) {
                $site->delete();

                return true;
            }
        }

        return  false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string|null $databaseName
     * @return bool
     */
    public function deleteForgeDatabaseIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        $featureDatabaseName = ForgeFormatter::getFeatureDatabase($siteName, $databaseName);

        $database = $this->searchDatabase($forgeServer, $featureDatabaseName);
        if ($database) {
            $database->delete();
            return true;
        }

        return false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string|null $databaseName
     * @return bool
     */
    public function deleteForgeDatabaseUserIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        $featureDatabaseUser = ForgeFormatter::getFeatureDatabaseUser($siteName, $databaseName);

        $databaseUser = $this->searchDatabaseUser($forgeServer, $featureDatabaseUser);
        if ($databaseUser) {
            $databaseUser->delete();
            return true;
        }

        return false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string $gitBranch
     * @param string|null $databaseName
     * @return Site
     */
    public function createForgeSite(Server $forgeServer, string $siteName, string $gitBranch, string $databaseName = null): Site
    {
        $featureDatabaseName = ForgeFormatter::getFeatureDatabase($siteName, $databaseName);
        $featureDatabaseUser = ForgeFormatter::getFeatureDatabaseUser($siteName, $databaseName);
        $featureDatabasePassword = ForgeFormatter::getFeatureDatabasePassword();
        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);

        $site = $this->forge->setTimeout(120)->createSite(
            $forgeServer->id,
            [
                "domain" => $featureDomain,
                "project_type" => "php",
                "aliases" => [],
                "directory" => '/public',
                "isolated" => false,
                "database" => $featureDatabaseName,
            ]
        );

        $database = $this->searchDatabase($forgeServer, $featureDatabaseName);

        $this->forge->createDatabaseUser($forgeServer->id, [
            "name" => $featureDatabaseUser,
            "password" => $featureDatabasePassword,
            "databases" => [$database->id]
        ], $wait = true);

        $site->installGitRepository([
            "provider" => "github",
            "repository" => config('radis.git_repository'),
            "branch" => $gitBranch,
            "composer" => false
        ]);

        $site->enableQuickDeploy();

        if (config('radis.forge.lets_encrypt_type') && config('radis.forge.lets_encrypt_api_key')) {
            $this->forge->obtainLetsEncryptCertificate($forgeServer->id, $site->id, [
                "domains" => [$featureDomain],
                "dns_provider" => [
                    "type" => config('radis.forge.lets_encrypt_type'),
                    "digitalocean_token" => config('radis.forge.lets_encrypt_api_key'),
                ],
            ]);
        }

        return $site;
    }

    /**
     * @param Server $forgeServer
     * @param Site $site
     * @param string $envContent
     */
    public function updateSiteEnvFile(Server $forgeServer, Site $site, string $envContent)
    {
        $this->forge->updateSiteEnvironmentFile($forgeServer->id, $site->id, $envContent);
    }

    /**
     * @param Server $forgeServer
     * @param int $siteId
     * @return Site|null
     */
    public function getSiteById(Server $forgeServer, int $siteId): ?Site
    {
        return $this->forge->site($forgeServer->id, $siteId);
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @return Site|null
     */
    public function getSiteBySiteName(Server $forgeServer, string $siteName): ?Site
    {
        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);
        foreach ($this->forge->sites($forgeServer->id) as $site) {
            if ($site->name === $featureDomain) {
                return $site;
            }
        }

        return null;
    }

    /**
     * @param Server $forgeServer
     * @param string $databaseName
     * @return Database|null
     */
    protected function searchDatabase(Server $forgeServer, string $databaseName): ?Database
    {
        foreach ($this->forge->databases($forgeServer->id) as $database) {
            if ($database->name === $databaseName) {
                return $database;
            }
        }

        return null;
    }

    /**
     * @param Server $forgeServer
     * @param string $databaseUser
     * @return DatabaseUser|null
     */
    protected function searchDatabaseUser(Server $forgeServer, string $databaseUser): ?DatabaseUser
    {
        foreach ($this->forge->databaseUsers($forgeServer->id) as $user) {
            if ($user->name === $databaseUser) {
                return $user;
            }
        }

        return null;
    }
}
