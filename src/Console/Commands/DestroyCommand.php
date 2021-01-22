<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Classes\ForgeFormatter;

class DestroyCommand extends ForgeAbstractCommand
{
    /** @var string */
    protected $signature = 'radis:destroy
                            {site_name : Name to set on forge}
                            {--database=} : Database name on forge';

    /** @var string */
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

        $hasDestroy = false;

        if ($this->forgeService->deleteForgeSiteIfExists($this->forgeServer, $siteName)) {
            $featureDomain = ForgeFormatter::getFeatureDomain($siteName);
            $this->comment('Deleting forge site : "'.$featureDomain.'"...');
            $hasDestroy = true;
        }

        if ($this->forgeService->deleteForgeDatabaseIfExists($this->forgeServer, $siteName, $databaseName)) {
            $featureDatabaseName = ForgeFormatter::getFeatureDatabase($siteName, $databaseName);
            $this->comment('Deleting forge database : "'.$featureDatabaseName.'"...');
            $hasDestroy = true;
        }

        if ($this->forgeService->deleteForgeDatabaseUserIfExists($this->forgeServer, $siteName, $databaseName)) {
            $featureDatabaseUser = ForgeFormatter::getFeatureDatabaseUser($siteName, $databaseName);
            $this->comment('Deleting forge database user : "'.$featureDatabaseUser.'"...');
            $hasDestroy = true;
        }

        if ($hasDestroy) {
            $this->info('Site ' . $siteName . ' fully destroyed !');

            return 0;
        }

        $this->info('Nothing to destroy');

        return 0;
    }
}
