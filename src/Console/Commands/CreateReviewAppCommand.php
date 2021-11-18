<?php

namespace WebId\Radis\Console\Commands;

use WebId\Radis\Classes\ForgeFormatter;
use WebId\Radis\Console\Commands\Traits\HasStub;
use WebId\Radis\Console\Commands\Traits\TranslateSiteName;
use WebId\Radis\Services\Exceptions\CouldNotObtainLetEncryptCertificateException;

class CreateReviewAppCommand extends ForgeAbstractCommand
{
    use HasStub;
    use TranslateSiteName;

    /** @var string */
    protected $signature = 'radis:create
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

        $scheme = 'https';
        $featureDomain = ForgeFormatter::getFeatureDomain($siteName);

        $this->destroyExisting($siteName, $databaseName);
        $this->waitingDestroy($siteName);

        $this->comment('Creating forge site : '.$featureDomain.' ...');

        $site = $this->forgeService->createForgeSite($this->forgeServer, $siteName, $gitBranch, $databaseName);

        try {
            $this->forgeService->createLetEncryptCertificate($this->forgeServer, $siteName, $site);
        } catch (CouldNotObtainLetEncryptCertificateException $e) {
            $scheme = 'http';
            $this->error(
                "Could not obtain let's encrypt certificate.\n" .
                "It can be because let's encrypt rate limit has been hit.\n" .
                $e->getMessage()
            );
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

        $this->comment('Waiting for site to be deployed...');
        $wait = true;
        $site->deploySite($wait);

        $this->info("The review app `${siteName}` has been created on branch `${gitBranch}`");
        $this->info("Access it with ${scheme}://${featureDomain}");

        return 0;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     */
    private function destroyExisting(string $siteName, string $databaseName = null): void
    {
        $commandArguments = [
            'site_name' => $siteName,
        ];

        if ($databaseName) {
            $commandArguments['--database'] = $databaseName;
        }

        $this->call('radis:destroy', $commandArguments);
    }

    /**
     * @param string $siteName
     * @throws \Exception
     */
    private function waitingDestroy(string $siteName): void
    {
        for ($i = 1; $i <= 10; $i++) {
            if ($this->getSiteByName($siteName) === null) {
                return;
            }
            sleep(1);
        }

        throw new \Exception('Site not destroy or lazy, try to restart command.');
    }
}
