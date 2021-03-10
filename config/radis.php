<?php

return [
    'driver' => env('RADIS_DRIVER'),
    'forge' => [
        'token' => env('FORGE_TOKEN'),
        'server_name' => env('FORGE_SERVER_NAME'),
        'server_domain' => env('FORGE_SERVER_DOMAIN'),
        'database_name' => env('FORGE_DATABASE_NAME'),
        'database_password' => env('FORGE_DATABASE_PASSWORD', 'root'),
        'lets_encrypt_type' => env('LETS_ENCRYPT_TYPE'),
        'lets_encrypt_api_key' => env('LETS_ENCRYPT_API_KEY'),
    ],
    'git_repository' => env('GIT_REPOSITORY')
];
