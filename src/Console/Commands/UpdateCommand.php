<?php

namespace WebId\Radis\Console\Commands;

class UpdateCommand extends ForgeAbstractCommand
{
    /** @var string */
    protected $signature = 'radis:update
                            {site_name : Site name on forge}
                            {--site=} : Site ID on forge';

    /** @var string */
    protected $description = 'Deploy existing Review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        parent::handle();

        /** @var string $siteName */
        $siteName = $this->argument('site_name');
        /** @var string $siteId */
        $siteId = $this->option('site');

        $site = $this->getSite($siteName, (int) $siteId);
        if (! $site) {
            return 0;
        }

        $this->comment("Send `${siteName}` deploying request ..");

        $site->deploySite(false);

        $this->info("The review app `${siteName}` will be deployed");

        return 0;
    }
}
