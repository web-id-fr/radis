<?php

return [
    'driver' => env('RADIS_DRIVER'),
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
