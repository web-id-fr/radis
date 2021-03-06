<?php

namespace WebId\Radis\Console\Commands\Traits;

use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;

trait HasStub
{
    /**
     * @param string $fileName
     * @return string
     */
    protected function getStub(string $fileName): string
    {
        $files = app(Filesystem::class);

        try {
            if ($files->exists(base_path("stubs/$fileName"))) {
                return $files->get(base_path("stubs/$fileName"));
            }

            return $files->get(__DIR__ . "/../../../Stubs/$fileName");
        } catch (\Exception $e) {
            report($e);

            return '';
        }
    }

    /**
     * @param string $stub
     * @param string $siteName
     * @return $this
     */
    public function replaceSiteName(string &$stub, string $siteName)
    {
        $stub = str_replace('STUB_SITE_NAME', $siteName, $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @return $this
     */
    public function replaceSiteKey(string &$stub)
    {
        $stub = str_replace('STUB_SITE_KEY', $this->generateRandomKey(), $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @param string $siteUrl
     * @return $this
     */
    public function replaceSiteUrl(string &$stub, string $siteUrl)
    {
        $stub = str_replace('STUB_SITE_URL', $siteUrl, $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @param string $databaseName
     * @return $this
     */
    public function replaceSiteDatabaseName(string &$stub, string $databaseName)
    {
        $stub = str_replace('STUB_SITE_DB_NAME', $databaseName, $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @param string $databaseUser
     * @return $this
     */
    public function replaceSiteDatabaseUser(string &$stub, string $databaseUser)
    {
        $stub = str_replace('STUB_SITE_DB_USER', $databaseUser, $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @param string $databasePassword
     * @return $this
     */
    public function replaceSiteDatabasePassword(string &$stub, string $databasePassword)
    {
        $stub = str_replace('STUB_SITE_DB_PASSWORD', $databasePassword, $stub);

        return $this;
    }

    /**
     * @param string $stub
     * @param string $gitBranch
     * @return $this
     */
    public function replaceGitBranch(string &$stub, string $gitBranch)
    {
        $stub = str_replace('STUB_GIT_BRANCH', $gitBranch, $stub);

        return $this;
    }

    /**
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(Config::get('app.cipher'))
        );
    }
}
