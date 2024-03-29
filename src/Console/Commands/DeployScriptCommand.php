<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Classes\ForgeFormatter;
use WebId\Radis\Console\Commands\Traits\HasStub;

class DeployScriptCommand extends ForgeAbstractCommand
{
    use HasStub;

    /** @var string */
    protected $signature = 'radis:deploy-script
                            {site_name : Name to set on forge}
                            {git_branch : Name of the git branch to deploy}
                            {--site=} : Site ID on forge';

    /** @var string */
    protected $description = 'Update deploy script on review App';

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

        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);

        $this->comment("Updating deploy script..");

        $deployScriptStub = $this->getStub('deployScript.stub');
        $this->replaceSiteUrl($deployScriptStub, $featureDomain)
            ->replaceGitBranch($deployScriptStub, $gitBranch);

        $site->updateDeploymentScript($deployScriptStub);

        $this->info("The review app `${siteName}` deploy script is updated !");

        return 0;
    }
}
