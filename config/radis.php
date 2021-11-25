<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RADIS Driver
    |--------------------------------------------------------------------------
    |
    | Allowed values :
    |   • "fake" to use the dummy service
    |   • leave blank in any other case
    |
    */

    'driver' => env('RADIS_DRIVER'),

    /*
    |--------------------------------------------------------------------------
    | Forge Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration explanation :
    |   • token
    |       • See the README.md file to know how to get this token
    |   • server_name
    |       • The name of the forge server you want to deploy on, find the list here: https://forge.laravel.com/servers/
    |   • server_domain
    |       • The main domain name, on which the review app will be deployed as a subdomain. Example: "mysite.demo.app"
    |   • site_php_version
    |       • The version of PHP you want to run on the server, use this format: "php80", "php74", ...
    |   • database_password
    |       • The password that will be used as a DB_PASSWORD on the review app
    |   • lets_encrypt_type
    |       • The DNS provider to use to get a Lets Encrypt certificate. Only "digitalocean" is allowed for now
    |   • lets_encrypt_api_key
    |       • The API token to use when requesting a certificate
    |
    */

    'forge' => [
        'token' => env('RADIS_TOKEN'),
        'server_name' => env('RADIS_SERVER_NAME'),
        'server_domain' => env('RADIS_SERVER_DOMAIN'),
        'site_php_version' => env('RADIS_SITE_VERSION', 'php80'),
        'database_password' => env('RADIS_DATABASE_PASSWORD', 'root'),
        'lets_encrypt_type' => env('RADIS_LETS_ENCRYPT_TYPE'),
        'lets_encrypt_api_key' => env('RADIS_LETS_ENCRYPT_API_KEY'),
        'timeout' => env('RADIS_FORGE_TIMEOUT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    |
    | The repository name of the project you want to deploy
    | Example: "web-id-fr/radis"
    |
    */

    'git_repository' => env('RADIS_GIT_REPOSITORY'),
];
