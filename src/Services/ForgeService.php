<?php

namespace WebId\Radis\Services;

use Illuminate\Support\Facades\Config;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Certificate;
use Laravel\Forge\Resources\Database;
use Laravel\Forge\Resources\DatabaseUser;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use WebId\Radis\Classes\ForgeFormatter;
use WebId\Radis\Services\Exceptions\CouldNotFindParentPreProductionSiteException;
use WebId\Radis\Services\Exceptions\CouldNotFindParentPreProductionSiteWildcardCertificateException;
use WebId\Radis\Services\Exceptions\CouldNotObtainLetEncryptCertificateException;

class ForgeService implements ForgeServiceContract
{
    // Default timeout to 10 minutes
    const DEFAULT_FORGE_TIMEOUT = 600;

    private Forge $forge;

    public function __construct()
    {
        $this->forge = new Forge(Config::get('radis.forge.token', ''));
        $this->forge->setTimeout(Config::get('radis.forge.timeout', self::DEFAULT_FORGE_TIMEOUT));
    }

    /**
     * @return Server
     */
    public function getForgeServer(): Server
    {
        $forgeServerName = Config::get('radis.forge.server_name');

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

        $site = $this->forge->createSite(
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

        /** @var Database $database */
        $database = $this->searchDatabase($forgeServer, $featureDatabaseName);

        $this->forge->createDatabaseUser($forgeServer->id, [
            "name" => $featureDatabaseUser,
            "password" => $featureDatabasePassword,
            "databases" => [$database->id],
        ], $wait = true);

        $site->changePHPVersion(config('radis.forge.site_php_version'));

        $site->installGitRepository([
            "provider" => "github",
            "repository" => Config::get('radis.git_repository'),
            "branch" => $gitBranch,
            "composer" => false,
        ]);

        return $site;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param Site $site
     */
    public function createLetEncryptCertificate(Server $forgeServer, $siteName, Site $site): void
    {
        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);
        if (! $this->hasCertificate($forgeServer, $site)) {
            try {
                $this->forge->obtainLetsEncryptCertificate($forgeServer->id, $site->id, [
                    "domains" => [$featureDomain],
                ]);
            } catch (\Throwable $e) {
                // this can happen if let's encrypt rate limit has been hit
                // it should not be blocking
                throw new CouldNotObtainLetEncryptCertificateException($e->getMessage(), 42, $e);
            }
        }
    }

    public function hasCertificate(Server $forgeServer, Site $site): bool
    {
        $certificates = $this->forge->certificates($forgeServer->id, $site->id);

        return ! empty($certificates);
    }

    /**
     * @param Server $forgeServer
     * @param Site $site
     * @param string $envContent
     */
    public function updateSiteEnvFile(Server $forgeServer, Site $site, string $envContent): void
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

    public function checkLastDeployment(Site $site): void
    {
        $deployments = $site->getDeploymentHistory();
        if (empty($deployments) || empty($deployments['deployments'])) {
            throw new \RuntimeException(sprintf(
                'No deployments found for site "%s".',
                $site->name
            ));
        }

        $lastDeployment = $deployments['deployments'][0];
        if ($lastDeployment['status'] === 'finished') {
            return;
        }

        $lastDeploymentOutput = $site->getDeploymentHistoryOutput($lastDeployment['id']);

        throw new \RuntimeException(sprintf(
            'Last deployment for site "%s" failed : %s',
            $site->name,
            "\n\n" . $lastDeploymentOutput['output'] ?? 'No output available.'
        ));
    }

    public function installParentPreProductionWebsiteWildcardCertificate(Server $forgeServer, Site $site): void
    {
        /** @var ?Site $parentPreProductionSite */
        $parentPreProductionSite = $this->getReviewAppPreProductionParentSiteId($site);

        if ($parentPreProductionSite === null) {
            // old pre-production websites do not use wildcard certificates,
            // we handle this case by creating a specific Let's Encrypt specific if it happens
            throw new CouldNotFindParentPreProductionSiteException(
                sprintf(
                    "Could not find parent pre-production website %s for review app %s",
                    Config::get('radis.forge.server_domain'),
                    $site->name
                )
            );
        }

        /** @var ?Certificate $wildcardCertificate */
        $wildcardCertificate = $this->getPreProductionSiteWildcardCertificate($parentPreProductionSite);

        if ($wildcardCertificate === null) {
            // old pre-production websites do not use wildcard certificates,
            // we handle this case by creating a specific Let's Encrypt specific if it happens
            throw new CouldNotFindParentPreProductionSiteWildcardCertificateException(
                sprintf(
                    "Could not find wildcard certificate on parent pre-production website %s" .
                    "for review app %s",
                    Config::get('radis.forge.server_domain'),
                    $site->name
                )
            );
        }

        $this->applyWildcardCertificateToNginxFile($parentPreProductionSite, $site, $wildcardCertificate);
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

    protected function getReviewAppPreProductionParentSiteId(site $reviewAppSite): ?Site
    {
        $preProductionDomain = explode('.', $reviewAppSite->name);
        array_shift($preProductionDomain);
        $preProductionDomain = implode('.', $preProductionDomain);

        foreach ($this->forge->sites($reviewAppSite->serverId) as $site) {
            if ($site->name === $preProductionDomain) {
                return $site;
            }
        }

        return null;
    }

    protected function getPreProductionSiteWildcardCertificate(Site $preProductionSite): ?Certificate
    {
        $certificates = $this->forge->certificates($preProductionSite->serverId, $preProductionSite->id);

        foreach ($certificates as $certificate) {
            $domains = $certificate->domain;

            foreach (explode(',', $domains) as $domain) {
                if ($domain === '*.' . $preProductionSite->name) {
                    return $certificate;
                }
            }
        }

        return null;
    }

    protected function applyWildcardCertificateToNginxFile(
        Site $preProductionSite,
        Site $reviewAppSite,
        Certificate $wildcardCertificate
    ): void {
        $nginxFileContent = $this->forge->siteNginxFile($reviewAppSite->serverId, $reviewAppSite->id);

        // replace port 80 with 443

        $nginxFileContent = str_replace(
            'listen 80;',
            'listen 443 ssl http2;',
            $nginxFileContent
        );

        $nginxFileContent = str_replace(
            'listen [::]:80;',
            'listen [::]:443 ssl http2;',
            $nginxFileContent
        );

        // append certificate lines before "   ssl_protocols (...)"

        $nginxFileContent = str_replace(
            "\n" . '    ssl_protocols',
            "\n" . '    # Wildcard from parent pre-production website' . "\n" .
            '    ssl_certificate /etc/nginx/ssl/' . $preProductionSite->name . '/' . $wildcardCertificate->id .
            '/server.crt;' . "\n" .
            '    ssl_certificate_key /etc/nginx/ssl/' . $preProductionSite->name . '/' . $wildcardCertificate->id .
            '/server.key;' . "\n\n" .
            '    ssl_protocols',
            $nginxFileContent
        );

        // it will automatically reload nginx
        $this->forge->updateSiteNginxFile($reviewAppSite->serverId, $reviewAppSite->id, $nginxFileContent);
    }
}
