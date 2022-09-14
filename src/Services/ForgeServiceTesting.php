<?php

namespace WebId\Radis\Services;

use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

class ForgeServiceTesting implements ForgeServiceContract
{
    public function getForgeServer(): Server
    {
        return $this->createFakeServer();
    }

    public function deleteForgeSiteIfExists(Server $forgeServer, string $siteName): bool
    {
        return true;
    }

    public function deleteForgeDatabaseIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        return true;
    }

    public function deleteForgeDatabaseUserIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        return true;
    }

    public function createForgeSite(Server $forgeServer, string $siteName, string $gitBranch, string $databaseName = null): Site
    {
        return $this->createFakeSite();
    }

    public function updateSiteEnvFile(Server $forgeServer, Site $site, string $envContent): void
    {
    }

    public function getSiteById(Server $forgeServer, int $siteId): ?Site
    {
        return $this->createFakeSite();
    }

    public function getSiteBySiteName(Server $forgeServer, string $siteName): ?Site
    {
        return $this->createFakeSite();
    }

    public function createLetEncryptCertificate(Server $forgeServer, $siteName, Site $site): void
    {
        // TODO: Implement createLetEncryptCertificate() method.
    }

    public function hasCertificate(Server $forgeServer, Site $site): bool
    {
        // TODO: Implement hasCertificate() method.
    }

    public function checkLastDeployment(Site $site): void
    {
        // TODO: Implement checkLastDeployment() method.
    }

    public function installParentPreProductionWebsiteWildcardCertificate(Server $forgeServer, Site $site): void
    {
        // TODO: Implement installParentPreProductionWebsiteWildcardCertificate() method.
    }

    protected function createFakeServer(): Server
    {
        $server = new Server([]);
        $server->id = 1;
        $server->credentialId = 1;
        $server->name = 'fakeServer';

        return $server;
    }

    protected function createFakeSite(): Site
    {
        $site = new Site([]);
        $site->id = 1;
        $site->serverId = 1;
        $site->name = 'fakeSite';

        return $site;
    }
}
