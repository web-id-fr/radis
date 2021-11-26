<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Console\Commands\Traits\HasStub;
use WebId\Radis\Console\Commands\Traits\TranslateSiteName;

class CreateOrUpdateCommand extends ForgeAbstractCommand
{
    use HasStub;
    use TranslateSiteName;

    /** @var string */
    protected $signature = 'radis:create-or-update
                            {site_name : Name to set on forge}
                            {git_branch : Name of the git branch to deploy}
                            {--database=} : Database name on forge';

    /** @var string */
    protected $description = 'Create a Review App';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle(): int
    {
        parent::handle();

        $this->checkConfig('radis.git_repository');

        /** @var string $siteName */
        $siteName = $this->argument('site_name');
        /** @var string $gitBranch */
        $gitBranch = $this->argument('git_branch');

        $siteName = $this->translateSiteName($siteName);

        /** @var string|null $databaseName */
        $databaseName = $this->option('database');

        $site = $this->forgeService->getSiteBySiteName($this->forgeServer, $siteName);

        // If the site does not exist or is not fully installed, we force the creation
        $siteExists = 
            $site &&
            $site->status === 'installed' && 
            $site->repositoryStatus === 'installed';

        if ($siteExists) {
            $this->info('Site exists, updating Review App');

            $this->call('radis:update', [
                'site_name' => $siteName,
            ]);
        } else {
            $this->info('Site does not exist, creating Review App');

            $this->call('radis:create', [
                'site_name' => $siteName,
                'git_branch' => $gitBranch,
                '--database' => $databaseName,
            ]);
        }

        return 0;
    }
}
