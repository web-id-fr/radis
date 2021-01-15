<?php

namespace WebId\Radis\Console\Commands;

use Laravel\Forge\Resources\Site;
use WebId\Radis\Console\Commands\Traits\HasStub;

class EnvCommand extends ForgeAbstractCommand
{
    use HasStub;

    /** @var string  */
    protected $signature = 'radis:env
                            {site_name : Name to set on forge}
                            {--database=} : Database name on forge
                            {--site=} : Site ID on forge';

    /** @var string  */
    protected $description = 'Update environment file on review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $siteName = $this->argument('site_name');
        $databaseName = $this->option('database');
        $siteId = $this->option('site');

        $site = $this->getSite($siteName, $siteId);
        if (!$site) {
            return 0;
        }

        $featureDomain = $this->forgeService->getFeatureDomain($siteName);
        $featureDatabaseName = $this->forgeService->getFeatureDatabase($siteName, $databaseName);
        $featureDatabaseUser = $this->forgeService->getFeatureDatabaseUser($siteName, $databaseName);
        $featureDatabasePassword = 'password';

        $envStub = $this->getStub('env.stub');
        $this->replaceSiteName($envStub, ucfirst($siteName))
            ->replaceSiteKey($envStub)
            ->replaceSiteUrl($envStub, 'https://' . $featureDomain)
            ->replaceSiteDatabaseName($envStub, $featureDatabaseName)
            ->replaceSiteDatabaseUser($envStub, $featureDatabaseUser)
            ->replaceSiteDatabasePassword($envStub, $featureDatabasePassword);
        $this->forgeService->updateSiteEnvFile($this->forgeServer, $site, $envStub);

        $this->info("The review app `${siteName}` environment is updated !");

        return 0;
    }
}
