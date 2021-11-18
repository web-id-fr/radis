<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Console\Commands\Traits\HasStub;
use WebId\Radis\Console\Commands\Traits\TranslateSiteName;

class GetUrlCommand extends ForgeAbstractCommand
{
    use HasStub;
    use TranslateSiteName;

    /** @var string */
    protected $signature = 'radis:get-url
                            {site_name : Name to set on forge}';

    /** @var string */
    protected $description = "Get a review app's url";

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle(): int
    {
        parent::handle();

        /** @var string $siteName */
        $siteName = $this->argument('site_name');

        $siteName = $this->translateSiteName($siteName);

        $site = $this->forgeService->getSiteBySiteName($this->forgeServer, $siteName);
        if (!$site) {
            $this->error('Site does not exist, could not get its url.');
            
            return 1;
        }

        $scheme = $this->forgeService->hasCertificate($this->forgeServer, $site) ? 'https' : 'http';

        $this->line(sprintf('%s://%s', $scheme, $site->name));

        return 0;
    }
}
