<?php

namespace WebId\Radis\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use WebId\Radis\RadisProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RadisProvider::class
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('radis.git_repository', 'git@github.com:fake/fake.git');
        config()->set('radis.driver', 'fake');
        config()->set('radis.forge', [
            'token' => 'thisIsFakeToken',
            'server_name' => 'fakeSite',
            'server_domain' => 'fake.ninja',
            'database_name' => 'fake',
            'database_password' => 'passwordVerySecure',
            'lets_encrypt_type' => 'digitalocean',
            'lets_encrypt_api_key' => 'fakeApiKey',
        ]);
    }
}
