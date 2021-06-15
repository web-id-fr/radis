# Radis - Review Apps Deployed In Seconds 😎

## Installation

Require this package with composer. It is recommended to only require the package for development.

```shell
composer require webid/radis --dev
```

Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Publish the package configuration and stubs with the publish command:

```shell
php artisan vendor:publish --provider="WebId\Radis\RadisProvider"
```

## Configurations

### 1. Configuration

You need to start by configuring your environment variables to access forge in ``config/radis.php``

First of all, create a new token here: https://forge.laravel.com/user/profile#/api and paste the generated copy in
the ``.env`` file:

```.dotenv
RADIS_TOKEN="my-brand-new-forge-token"
```

```php
return [
    'forge' => [
        'token' => env('RADIS_TOKEN'),
        'server_name' => env('RADIS_SERVER_NAME'),
        'server_domain' => env('RADIS_SERVER_DOMAIN'),
        'site_php_version' => env('RADIS_SITE_VERSION', 'php80'),
        'database_password' => env('RADIS_DATABASE_PASSWORD', 'root'),
        'lets_encrypt_type' => env('RADIS_LETS_ENCRYPT_TYPE'),
        'lets_encrypt_api_key' => env('RADIS_LETS_ENCRYPT_API_KEY'),
    ],
    
    'git_repository' => env('RADIS_GIT_REPOSITORY')
];
```

`lets_encrypt_type` and `lets_encrypt_api_key` are not required, but it's needed for auto HTTPS. For digitalocean
example (https://docs.digitalocean.com/reference/api/create-personal-access-token/):

```dotenv
RADIS_LETS_ENCRYPT_TYPE=digitalocean
RADIS_LETS_ENCRYPT_API_KEY=EXEMPLE98edb566f9917d797fba2c0b05e2f2064ad7771422740181561322961
```

### 2. ``.env`` stub

After that, you need to adapt the desired .env file for your review app by modifying the stub ``stubs/env.stub``

Don't change the constants starting with ``STUB_``, they will be automatically replaced according to your configuration,
or the parameters given to artisan commands.

### 3. Deploy script stub

Finally, you need to adapt the forge deployment script according to your project in the stub ``stubs/deployScript.stub``

## Usage

### Create a review app

> ⚠️ If a review app already exists with this name, it will be destroyed and recreated

```shell
php artisan radis:create mySiteName myGitBranch
php artisan radis:create mySiteName myGitBranch customDatabaseName
```

### Update an existing review app

This will only launch the deploy script

```shell
php artisan radis:update mySiteName
```

### Destroy a review app

> ⚠️ This will remove both database and associated user database

```shell
php artisan radis:destroy mySiteName
```

### Update the ``.env`` file

```shell
php artisan radis:env mySiteName
php artisan radis:env mySiteName customDatabaseName
```

### Update the deploy script

> ℹ️ It updates the script without running it

```shell
php artisan radis:deploy-script mySiteName myGitBranch
```
