<?php

namespace WebId\Radis\Console\Commands;

class DestroyCommand extends ForgeAbstractCommand
{
    /** @var string  */
    protected $signature = 'radis:destroy
                            {site_name : Name to set on forge}
                            {--database=} : Database name on forge';

    /** @var string  */
    protected $description = 'Destroy a Review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $siteName = $this->argument('site_name');
        $databaseName = $this->option('database');

        if ($this->forgeService->deleteForgeSiteIfExists($this->forgeServer, $siteName)) {
            $featureDomain = $this->forgeService->getFeatureDomain($siteName);
            $this->comment('Deleting forge site : "'.$featureDomain.'"...');
        }

        if ($this->forgeService->deleteForgeDatabaseIfExists($this->forgeServer, $siteName, $databaseName)) {
            $featureDatabaseName = $this->forgeService->getFeatureDatabase($siteName, $databaseName);
            $this->comment('Deleting forge database : "'.$featureDatabaseName.'"...');
        }

        if ($this->forgeService->deleteForgeDatabaseUserIfExists($this->forgeServer, $siteName, $databaseName)) {
            $featureDatabaseUser = $this->forgeService->getFeatureDatabaseUser($siteName, $databaseName);
            $this->comment('Deleting forge database user : "'.$featureDatabaseUser.'"...');
        }

        return 0;
    }
}
