<?php

namespace WebId\Radis\Console\Commands;

class UpdateCommand extends ForgeAbstractCommand
{
    /** @var string */
    protected $signature = 'radis:update
                            {site_name : Site name on forge}
                            {git_branch : Name of the git branch to deploy}
                            {--site=} : Site ID on forge';

    /** @var string */
    protected $description = 'Deploy existing Review App';

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
        /** @var string $gitBranch */
        $gitBranch = $this->argument('git_branch');
        /** @var string $siteId */
        $siteId = $this->option('site');

        $site = $this->getSite($siteName, (int) $siteId);
        if (! $site) {
            return 0;
        }

        $this->comment('Updating site env...');
        $this->call('radis:env', [
            'site_name' => $siteName,
        ]);

        $this->comment('Updating site script deploy...');
        $this->call('radis:deploy-script', [
            'site_name' => $siteName,
            'git_branch' => $gitBranch,
        ]);

        $this->comment("Waiting for `${siteName}` to be deployed...");

        // Waiting for site to be deployed
        $site = $site->deploySite(true);

        $this->comment('Checking last deployment...');
        $this->forgeService->checkLastDeployment($site);

        $this->info("The review app `${siteName}` has been updated");

        return 0;
    }
}
