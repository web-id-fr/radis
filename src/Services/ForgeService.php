<?php

namespace WebId\Radis\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Database;
use Laravel\Forge\Resources\DatabaseUser;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

class ForgeService
{
    /** @var Forge  */
    private $forge;

    public function __construct()
    {
        $this->forge = new Forge(config('radis.forge.token'));
    }

    /**
     * @return Server
     */
    public function getForgeServer(): Server
    {
        $forgeServerName = config('radis.forge.server_name');

        foreach ($this->forge->servers() as $server) {
            if ($server->name === $forgeServerName) {
                return $server;
            }
        }

        throw new \RuntimeException(sprintf(
            'Forge server with name "%s" was not found.',
            $forgeServerName
        ));
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @return bool
     */
    public function deleteForgeSiteIfExists(Server $forgeServer, string $siteName): bool
    {
        $featureDomain = $this->getFeatureDomain($siteName);
        foreach ($this->forge->sites($forgeServer->id) as $site) {
            if ($site->name === $featureDomain) {
                $site->delete();

                return true;
            }
        }

        return  false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string|null $databaseName
     * @return bool
     */
    public function deleteForgeDatabaseIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        $featureDatabaseName = $this->getFeatureDatabase($siteName, $databaseName);

        $database = $this->searchDatabase($forgeServer, $featureDatabaseName);
        if ($database) {
            $database->delete();
            return true;
        }

        return false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string|null $databaseName
     * @return bool
     */
    public function deleteForgeDatabaseUserIfExists(Server $forgeServer, string $siteName, string $databaseName = null): bool
    {
        $featureDatabaseUser = $this->getFeatureDatabaseUser($siteName, $databaseName);

        $databaseUser = $this->searchDatabaseUser($forgeServer, $featureDatabaseUser);
        if ($databaseUser) {
            $databaseUser->delete();
            return true;
        }

        return false;
    }

    /**
     * @param Server $forgeServer
     * @param string $siteName
     * @param string $gitBranch
     * @param string|null $databaseName
     * @return Site
     */
    public function createForgeSite(Server $forgeServer, string $siteName, string $gitBranch, string $databaseName = null): Site
    {
        $featureDatabaseName = $this->getFeatureDatabase($siteName, $databaseName);
        $featureDatabaseUser = $this->getFeatureDatabaseUser($siteName, $databaseName);
        $featureDatabasePassword = $this->getFeatureDatabasePassword();

        $site = $this->forge->setTimeout(120)->createSite(
            $forgeServer->id,
            [
                "domain" => $this->getFeatureDomain($siteName),
                "project_type" => "php",
                "aliases" => [],
                "directory" => '/public',
                "isolated" => false,
                "database" => $featureDatabaseName,
            ]
        );

        $database = $this->searchDatabase($forgeServer, $featureDatabaseName);

        $this->forge->createDatabaseUser($forgeServer->id, [
            "name" => $featureDatabaseUser,
            "password" => $featureDatabasePassword,
            "databases" => [$database->id]
        ], $wait = true);

        $site->installGitRepository([
            "provider" => "github",
            "repository" => config('radis.git_repository'), // @TODO env // @TODO vérifier que la branche existe
            "branch" => $gitBranch,
            "composer" => false
        ]);

        $envContent = <<<DOTENV
            APP_NAME=Laravel
            APP_ENV=production
            APP_KEY=base64:jr7NYQfs6RIlKxXNhYjZXORTt1D68tmoy2GGfgrmOWI=
            APP_DEBUG=false
            APP_URL=http://localhost

            LOG_CHANNEL=daily

            DB_CONNECTION=pgsql
            DB_HOST=127.0.0.1
            DB_PORT=5432
            DB_DATABASE=${featureDatabaseName}
            DB_USERNAME=${featureDatabaseUser}
            DB_PASSWORD=${featureDatabasePassword}

            BROADCAST_DRIVER=log
            CACHE_DRIVER=file
            QUEUE_CONNECTION=sync
            SESSION_DRIVER=file
            SESSION_LIFETIME=120

            REDIS_HOST=127.0.0.1
            REDIS_PASSWORD=""
            REDIS_PORT=6379

            MAIL_MAILER=smtp
            MAIL_HOST=smtp.mailtrap.io
            MAIL_PORT=2525
            MAIL_USERNAME=null
            MAIL_PASSWORD=null
            MAIL_ENCRYPTION=null
            MAIL_FROM_ADDRESS=null
            MAIL_FROM_NAME="\${APP_NAME}"

            AWS_ACCESS_KEY_ID=
            AWS_SECRET_ACCESS_KEY=
            AWS_DEFAULT_REGION=us-east-1
            AWS_BUCKET=

            PUSHER_APP_ID=
            PUSHER_APP_KEY=
            PUSHER_APP_SECRET=
            PUSHER_APP_CLUSTER=mt1

            MIX_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
            MIX_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

            DOTENV;

        $this->forge->updateSiteEnvironmentFile($forgeServer->id, $site->id, $envContent);

        $deploymentScript = <<<BASH
            cd /home/forge/$siteName.ireadit.io
            git fetch
            git reset --hard origin/$gitBranch


            ( flock -w 10 9 || exit 1
                echo 'Restarting FPM...'; sudo -S service \$FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

            make reset
            BASH;

        $site->updateDeploymentScript($deploymentScript);
        $site->enableQuickDeploy();

//        $this->forge->obtainLetsEncryptCertificate($forgeServer->id, $site->id, [
//            "domains" => [$siteName.'.ireadit.io'],
//            "dns_provider" => [
//                "type" => "digitalocean",
//                "digitalocean_token" => config('radis.forge.digital_ocean_api_key'),
//            ],
//        ]);
//        dump(6);

        $site->deploySite();

        return $site;
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public function getFeatureDatabase(string $siteName, string $databaseName = null): string
    {
        return $databaseName ?? $siteName . 'db';
    }

    /**
     * @param string $siteName
     * @param string|null $databaseName
     * @return string
     */
    public function getFeatureDatabaseUser(string $siteName, string $databaseName = null): string
    {
        return $this->getFeatureDatabase($siteName, $databaseName) . 'user';
    }

    /**
     * @param string $siteName
     * @return string
     */
    public function getFeatureDomain(string $siteName): string
    {
        return $siteName . '-feature.' . config('radis.forge.server_domain');
    }

    /**
     * @return string
     */
    private function getFeatureDatabasePassword(): string
    {
        return config('radis.forge.database_password', Hash::make(Str::random(12)));
    }

    /**
     * @param Server $forgeServer
     * @param string $databaseName
     * @return Database|null
     */
    protected function searchDatabase(Server $forgeServer, string $databaseName): ?Database
    {
        foreach ($this->forge->databases($forgeServer->id) as $database) {
            if ($database->name === $databaseName) {
                return $database;
            }
        }

        return null;
    }

    protected function searchDatabaseUser(Server $forgeServer, string $databaseUser): ?DatabaseUser
    {
        foreach ($this->forge->databaseUsers($forgeServer->id) as $user) {
            if ($user->name === $databaseUser) {
                return $user;
            }
        }

        return null;
    }
}
