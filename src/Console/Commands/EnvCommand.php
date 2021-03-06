<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Classes\ForgeFormatter;
use WebId\Radis\Console\Commands\Traits\HasStub;

class EnvCommand extends ForgeAbstractCommand
{
    use HasStub;

    /** @var string */
    protected $signature = 'radis:env
                            {site_name : Name to set on forge}
                            {--database=} : Database name on forge
                            {--site=} : Site ID on forge';

    /** @var string */
    protected $description = 'Update environment file on review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        parent::handle();

        /** @var string $siteName */
        $siteName = $this->argument('site_name');
        /** @var string $databaseName */
        $databaseName = $this->option('database');
        /** @var string $siteId */
        $siteId = $this->option('site');

        $site = $this->getSite($siteName, (int) $siteId);
        if (! $site) {
            return 0;
        }

        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);
        $featureDatabaseName = ForgeFormatter::getFeatureDatabase($siteName, $databaseName);
        $featureDatabaseUser = ForgeFormatter::getFeatureDatabaseUser($siteName, $databaseName);
        $featureDatabasePassword = ForgeFormatter::getFeatureDatabasePassword();

        $this->comment("Updating environment..");

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
