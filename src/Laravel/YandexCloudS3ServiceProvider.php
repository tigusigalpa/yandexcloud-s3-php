<?php

namespace Tigusigalpa\YandexCloudS3\Laravel;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Tigusigalpa\YandexCloudS3\S3Client;
use Tigusigalpa\YandexCloudS3\BucketManagementClient;

/**
 * Laravel service provider for Yandex Cloud S3
 */
class YandexCloudS3ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/yandexcloud-s3.php',
            'yandexcloud-s3'
        );

        $this->app->singleton('yandexcloud-s3', function ($app) {
            $config = $app['config']['yandexcloud-s3'];

            // Configuration validation
            if (empty($config['oauth_token'])) {
                throw new InvalidArgumentException(
                    'Yandex Cloud S3 OAuth token is not configured. ' .
                    'Add YANDEX_CLOUD_OAUTH_TOKEN to .env file'
                );
            }

            if (empty($config['bucket'])) {
                throw new InvalidArgumentException(
                    'Yandex Cloud S3 bucket name is not configured. ' .
                    'Add YANDEX_CLOUD_BUCKET to .env file'
                );
            }

            return new S3Client(
                $config['oauth_token'],
                $config['bucket'],
                $config['endpoint'] ?? null,
                $config['options'] ?? []
            );
        });

        $this->app->singleton('yandexcloud-s3-bucket-management', function ($app) {
            $config = $app['config']['yandexcloud-s3'];

            // Configuration validation
            if (empty($config['oauth_token'])) {
                throw new InvalidArgumentException(
                    'Yandex Cloud S3 OAuth token is not configured. ' .
                    'Add YANDEX_CLOUD_OAUTH_TOKEN to .env file'
                );
            }

            if (empty($config['folder_id'])) {
                throw new InvalidArgumentException(
                    'Yandex Cloud folder ID is not configured. ' .
                    'Add YANDEX_CLOUD_FOLDER_ID to .env file'
                );
            }

            return new BucketManagementClient(
                $config['oauth_token'],
                $config['folder_id']
            );
        });

        $this->app->alias('yandexcloud-s3', S3Client::class);
        $this->app->alias('yandexcloud-s3-bucket-management', BucketManagementClient::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/yandexcloud-s3.php' => config_path('yandexcloud-s3.php'),
            ], 'yandexcloud-s3-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'yandexcloud-s3',
            'yandexcloud-s3-bucket-management',
            S3Client::class,
            BucketManagementClient::class,
        ];
    }
}
