<?php

namespace WebId\Radis\Console\Commands;

class DeployCommand extends ForgeAbstractCommand
{
    /** @var string  */
    protected $signature = 'radis:update
                            {site_name : Site name on forge}
                            {--site=} : Site ID on forge';

    /** @var string  */
    protected $description = 'Deploy existing Review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $siteName = $this->argument('site_name');

        $siteId = $this->option('site');

        $site = $this->getSite($siteName, $siteId);
        if (!$site) {
            return 0;
        }

        $this->comment("Send `${siteName}` deploying request ..");

        $site->deploySite(false);

        $this->info("The review app `${siteName}` will be deployed");

    }

    /**
     * @return int
     */
    protected function getCountTask(): int
    {
        return 0;
    }
}
