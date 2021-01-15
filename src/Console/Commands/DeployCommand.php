<?php

namespace WebId\Radis\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use WebId\Radis\Console\Commands\Traits\CheckConfig;
use WebId\Radis\Services\ForgeService;

class DeployCommand extends Command
{
    use CheckConfig;

    /** @var string  */
    protected $signature = 'radis:deploy
                            {site_name : Name to set on forge}
                            {git_branch : Name of the git branch to deploy}
                            {--database=} : Database name on forge';

    /** @var string  */
    protected $description = 'Deploy a Review App';

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
        $this->checkConfig('radis.forge.digital_ocean_api_key');
        $this->checkConfig('radis.git_repository');

        $gitBranch = $this->argument('git_branch');
        $siteName = $this->argument('site_name');
        $databaseName = $this->option('database');

        $forgeServer = $this->forgeService->getForgeServer();

        $this->destroyExisting($siteName, $databaseName);

        $featureDomain = $this->forgeService->getFeatureDomain($siteName);
        $this->info('Creating forge site : "'.$featureDomain.'"...');
        $this->forgeService->createForgeSite($forgeServer, $siteName, $gitBranch, $databaseName);

        $this->info("The review app `${siteName}` will be created with the branch `${gitBranch}`");

        return 0;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     */
    private function destroyExisting(string $siteName, string $databaseName = null)
    {
        $commandDestroy = [
            'site_name' => $siteName,
        ];
        if ($databaseName) {
            $commandDestroy['--database'] = $databaseName;
        }
        $this->call('radis:destroy', $commandDestroy);
    }
}
