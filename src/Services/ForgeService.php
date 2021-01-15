<?php

namespace WebId\Radis\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Database;
use Laravel\Forge\Resources\DatabaseUser;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

class ForgeService
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
        $featureDomain = $this->getFeatureDomain($siteName);
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
        $featureDatabaseName = $this->getFeatureDatabase($siteName, $databaseName);

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
        $featureDatabaseUser = $this->getFeatureDatabaseUser($siteName, $databaseName);

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
        $featureDatabaseName = $this->getFeatureDatabase($siteName, $databaseName);
        $featureDatabaseUser = $this->getFeatureDatabaseUser($siteName, $databaseName);
        $featureDatabasePassword = $this->getFeatureDatabasePassword();
        $featureDomain = $this->getFeatureDomain($siteName);

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
            "repository" => config('radis.git_repository'), // @TODO env // @TODO vérifier que la branche existe
            "branch" => $gitBranch,
            "composer" => false
        ]);

//        $this->forge->obtainLetsEncryptCertificate($forgeServer->id, $site->id, [
//            "domains" => [$featureDomain],
//            "dns_provider" => [
//                "type" => "digitalocean",
//                "digitalocean_token" => config('radis.forge.digital_ocean_api_key'),
//            ],
//        ]);

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
        $featureDomain = $this->getFeatureDomain($siteName);
        foreach ($this->forge->sites($forgeServer->id) as $site) {
            if ($site->name === $featureDomain) {
                return $site;
            }
        }

        return null;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public function getFeatureDatabase(string $siteName, string $databaseName = null): string
    {
        return $databaseName ?? $siteName . 'db';
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public function getFeatureDatabaseUser(string $siteName, string $databaseName = null): string
    {
        return $this->getFeatureDatabase($siteName, $databaseName) . 'user';
    }

    /**
     * @param string $siteName
     * @return string
     */
    public function getFeatureDomain(string $siteName): string
    {
        return $siteName . '-feature.' . config('radis.forge.server_domain');
    }

    /**
     * @return string
     */
    private function getFeatureDatabasePassword(): string
    {
        return config('radis.forge.database_password', Hash::make(Str::random(12)));
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
