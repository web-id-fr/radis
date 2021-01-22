<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Console\Commands\Traits\CheckGitBranch;
use WebId\Radis\Console\Commands\Traits\HasStub;

class CreateReviewAppCommand extends ForgeAbstractCommand
{
    use HasStub,
        CheckGitBranch;

    /** @var string  */
    protected $signature = 'radis:create
                            {site_name : Name to set on forge}
                            {git_branch : Name of the git branch to deploy}
                            {--database=} : Database name on forge';

    /** @var string  */
    protected $description = 'Create a Review App';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->checkConfig('radis.git_repository');

        $siteName = $this->argument('site_name');
        $gitBranch = $this->argument('git_branch');
        if (!$this->checkGitBranch($gitBranch)) {
            return 0;
        }
        $databaseName = $this->option('database');

        $featureDomain = $this->forgeService->getFeatureDomain($siteName);

        $this->destroyExisting($siteName, $databaseName);

        $this->info('Creating forge site : "'.$featureDomain.'"...');
        $site = $this->forgeService->createForgeSite($this->forgeServer, $siteName, $gitBranch, $databaseName);

        $this->callSilent('radis:env', [
            'site_name' => $siteName,
            '--site' => $site->id
        ]);

        $this->callSilent('radis:deploy-script', [
            'site_name' => $siteName,
            'git_branch' => $gitBranch,
            '--site' => $site->id
        ]);

        $site->deploySite();

        $this->info("The review app `${siteName}` will be created with the branch `${gitBranch}`");

        return 0;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     */
    private function destroyExisting(string $siteName, string $databaseName = null)
    {
        $commandDestroy = [
            'site_name' => $siteName,
        ];
        if ($databaseName) {
            $commandDestroy['--database'] = $databaseName;
        }
        $this->call('radis:destroy', $commandDestroy);
    }
}
