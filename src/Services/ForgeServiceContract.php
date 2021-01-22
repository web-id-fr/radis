<?php

namespace WebId\Radis\Services;

use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

interface ForgeServiceContract
{
    public function getForgeServer(): Server;

    public function deleteForgeSiteIfExists(Server $forgeServer, string $siteName): bool;

    public function deleteForgeDatabaseIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool;

    public function deleteForgeDatabaseUserIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool;

    public function createForgeSite(Server $forgeServer, string $siteName, string $gitBranch, string $databaseName = null): Site;

    public function updateSiteEnvFile(Server $forgeServer, Site $site, string $envContent);

    public function getSiteById(Server $forgeServer, int $siteId): ?Site;

    public function getSiteBySiteName(Server $forgeServer, string $siteName): ?Site;
}
