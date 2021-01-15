<?php

return [
    'forge' => [
        'token' => env('FORGE_TOKEN'),
        'server_name' => env('FORGE_SERVER_NAME'),
        'server_domain' => env('FORGE_SERVER_DOMAIN'),
        'database_name' => env('FORGE_DATABASE_NAME'),
        'database_password' => env('FORGE_DATABASE_PASSWORD'),
        'digital_ocean_api_key' => env('DIGITAL_OCEAN_API_KEY'),
    ],
    'git_repository' => env('GIT_REPOSITORY')
];
