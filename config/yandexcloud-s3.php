<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Yandex Cloud OAuth Token
    |--------------------------------------------------------------------------
    |
    | OAuth token for accessing Yandex Cloud API.
    | Can be obtained from:
    | https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb
    |
    */
    'oauth_token' => env('YANDEX_CLOUD_OAUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Bucket Name
    |--------------------------------------------------------------------------
    |
    | Default S3 bucket name for file operations.
    | Can be overridden per operation.
    |
    */
    'bucket' => env('YANDEX_CLOUD_BUCKET'),

    /*
    |--------------------------------------------------------------------------
    | Folder ID
    |--------------------------------------------------------------------------
    |
    | Yandex Cloud folder ID for bucket management operations.
    | Required for REST API bucket management.
    |
    */
    'folder_id' => env('YANDEX_CLOUD_FOLDER_ID'),

    /*
    |--------------------------------------------------------------------------
    | S3 Endpoint
    |--------------------------------------------------------------------------
    |
    | Yandex Cloud Object Storage endpoint.
    | Default: https://storage.yandexcloud.net
    |
    */
    'endpoint' => env('YANDEX_CLOUD_ENDPOINT', 'https://storage.yandexcloud.net'),

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | AWS region for compatibility. Yandex Cloud uses ru-central1.
    |
    */
    'region' => env('YANDEX_CLOUD_REGION', 'ru-central1'),

    /*
    |--------------------------------------------------------------------------
    | Additional AWS SDK Options
    |--------------------------------------------------------------------------
    |
    | Additional options to pass to AWS SDK S3Client.
    | https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-examples.html
    |
    */
    'options' => [
        'http' => [
            'timeout' => (int) env('YANDEX_CLOUD_TIMEOUT', 30),
            'connect_timeout' => (int) env('YANDEX_CLOUD_CONNECT_TIMEOUT', 10),
        ],
    ],
];
