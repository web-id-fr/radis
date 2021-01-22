<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Console\Commands\Traits\CheckGitBranch;
use WebId\Radis\Console\Commands\Traits\HasStub;

class DeployScriptCommand extends ForgeAbstractCommand
{
    use HasStub,
        CheckGitBranch;

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
    public function handle()
    {
        $siteName = $this->argument('site_name');
        $gitBranch = $this->argument('git_branch');
        $siteId = $this->option('site');
        if (!$this->checkGitBranch($gitBranch)) {
            return 0;
        }

        $site = $this->getSite($siteName, $siteId);
        if (!$site) {
            return 0;
        }

        $featureDomain = $this->forgeService->getFeatureDomain($siteName);

        $this->comment("Updating deploy script..");

        $deployScriptStub = $this->getStub('deployScript.stub');
        $this->replaceSiteUrl($deployScriptStub, $featureDomain)
            ->replaceGitBranch($deployScriptStub, $gitBranch);

        $site->updateDeploymentScript($deployScriptStub);

        $this->info("The review app `${siteName}` deploy script is updated !");

        return 0;
    }
}
