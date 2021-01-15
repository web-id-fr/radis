<?php

namespace WebId\Radis\Console\Commands;

use Illuminate\Console\Command;
use WebId\Radis\Console\Commands\Traits\CheckConfig;
use WebId\Radis\Services\ForgeService;

class DestroyCommand extends Command
{
    use CheckConfig;

    /** @var string  */
    protected $signature = 'radis:destroy
                            {site_name : Name to set on forge}
                            {--database=} : Database name on forge';

    /** @var string  */
    protected $description = 'Destroy a Review App';

    /** @var ForgeService  */
    protected $forgeService;

    /**
     * @param ForgeService $forgeService
     */
    public function __construct(ForgeService $forgeService)
    {
        parent::__construct();

        $this->forgeService = $forgeService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->checkConfig('radis.forge.token');
        $this->checkConfig('radis.forge.server_name');
        $this->checkConfig('radis.forge.server_domain');

        $siteName = $this->argument('site_name');
        $databaseName = $this->option('database');

        $forgeServer = $this->forgeService->getForgeServer();

        if ($this->forgeService->deleteForgeSiteIfExists($forgeServer, $siteName)) {
            $featureDomain = $this->forgeService->getFeatureDomain($siteName);
            $this->comment('Deleting forge site : "'.$featureDomain.'"...');
        }

        if ($this->forgeService->deleteForgeDatabaseIfExists($forgeServer, $siteName, $databaseName)) {
            $featureDatabaseName = $this->forgeService->getFeatureDatabase($siteName, $databaseName);
            $this->comment('Deleting forge database : "'.$featureDatabaseName.'"...');
        }

        if ($this->forgeService->deleteForgeDatabaseUserIfExists($forgeServer, $siteName, $databaseName)) {
            $featureDatabaseUser = $this->forgeService->getFeatureDatabaseUser($siteName, $databaseName);
            $this->comment('Deleting forge database user : "'.$featureDatabaseUser.'"...');
        }

        return 0;
    }
}
