<?php

namespace WebId\Radis\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;
use WebId\Radis\Console\Commands\Traits\CheckConfig;
use WebId\Radis\Services\ForgeService;

abstract class ForgeAbstractCommand extends Command
{
    use CheckConfig;

    /** @var ForgeService */
    protected $forgeService;

    /** @var Server */
    protected $forgeServer;

    /**
     * @param ForgeService $forgeService
     */
    public function __construct(ForgeService $forgeService)
    {
        parent::__construct();

        $this->forgeService = $forgeService;
        $this->forgeServer = $this->forgeService->getForgeServer();

        $this->checkConfig('radis.forge.token');
        $this->checkConfig('radis.forge.server_name');
        $this->checkConfig('radis.forge.server_domain');
    }

    /**
     * @param string $siteName
     * @param int|null $siteId
     * @return Site|null
     */
    protected function getSite(string $siteName, ?int $siteId): ?Site
    {
        if (!empty($siteId) && is_int($siteId)) {
            return $this->getSiteById($siteId);
        }

        return $this->getSiteByName($siteName);
    }

    /**
     * @param int $siteId
     * @return Site|null
     */
    protected function getSiteByid(int $siteId): ?Site
    {
        try {
            return $this->forgeService->getSiteById($this->forgeServer, $siteId);
        } catch (\Exception $e) {
            report($e);
            $this->error('No site found with this ID : ' . $siteId);
            if (! $this->confirm('Would you like to try site name instead site ID ?')) {
                return null;
            }
        }
    }

    /**
     * @param string $siteName
     * @return Site|null
     */
    protected function getSiteByName(string $siteName): ?Site
    {
        try {
            return $this->forgeService->getSiteBySiteName($this->forgeServer, $siteName);
        } catch (\Exception $e) {
            report($e);
            $this->error('No site found with this site name : ' . $siteName);

            return null;
        }
    }
}
