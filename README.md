# Radis - Review Apps Deployed In Seconds 😎

## Installation

Require this package with composer. It is recommended to only require the package for development.

```shell
composer require webid/radis --dev
```
Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Copy the package config to your local config and stubs with the publish command:
```shell
php artisan vendor:publish --provider="WebId\Radis\RadisProvider"
```

## Configurations

### 1/ Config
You need to start by configuring your environment variables to access forge in ``config/radis.php``

```
return [
    'forge' => [
        'token' => env('RADIS_TOKEN'), //https://forge.laravel.com/user/profile#/api
        'server_name' => env('RADIS_SERVER_NAME'), //example: link
        'server_domain' => env('RADIS_SERVER_DOMAIN'), //example: link.hyrule.org
        'site_php_version' => env('RADIS_SITE_VERSION', 'php80'), //examples: 'php80' or 'php74'
        'database_password' => env('RADIS_DATABASE_PASSWORD', 'root'),
        'lets_encrypt_type' => env('RADIS_LETS_ENCRYPT_TYPE'),
        'lets_encrypt_api_key' => env('RADIS_ENCRYPT_API_KEY'),
    ],
    'git_repository' => env('RADIS_GIT_REPOSITORY')
];
```

`lets_encrypt_type` and `lets_encrypt_api_key` are not required, but it's needed for auto HTTPS.
For digitalocean example (https://docs.digitalocean.com/reference/api/create-personal-access-token/): 
```
RADIS_LETS_ENCRYPT_TYPE=digitalocean
RADIS_ENCRYPT_API_KEY=EXEMPLE98edb566f9917d797fba2c0b05e2f2064ad7771422740181561322961
```

### 2/ Stub Env
After that, you need to adapt the desired .env file for your review app by modifying the stub ``stubs/env.stub``
Variables starting with ``STUB_`` are automatically replaced according to your configuration, or the parameters given to artisan commands.

### 3/ Stub Deploy script

Finally, you need to adapt the forge deployment script according to your project in the stub ``stubs/deployScript.stub``

## Usage

You can now deploy a review app juste with :

```
php artisan radis:create mySiteName myGitBranch
php artisan radis:create mySiteName myGitBranch customDatabaseName
```

Or update an existing review app (just launch the deploy script) :
```
php artisan radis:update mySiteName
```
And destroy an review app

!!! WARNING !!! This will remove database and user database associated
```
php artisan radis:destroy mySiteName
```
You can also only update the .env
```
php artisan radis:env mySiteName
php artisan radis:env mySiteName customDatabaseName
```
Or the deploy script (without launching it)
```
php artisan radis:deploy-script mySiteName myGitBranch
```
