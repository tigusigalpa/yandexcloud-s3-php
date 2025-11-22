<?php

namespace Tigusigalpa\YandexCloudS3\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tigusigalpa\YandexCloudS3\S3Client;

/**
 * @method static \Tigusigalpa\YandexCloudS3\Models\S3Object[] listObjects(?string $prefix = null, int $maxKeys = 1000)
 * @method static bool exists(string $key, ?string $bucket = null)
 * @method static \Tigusigalpa\YandexCloudS3\Models\S3Object getObjectMetadata(string $key, ?string $bucket = null)
 * @method static string getObject(string $key, ?string $bucket = null)
 * @method static \Tigusigalpa\YandexCloudS3\Models\S3Object putObject(string $key, $body, array $options = [], ?string $bucket = null)
 * @method static bool deleteObject(string $key, ?string $bucket = null)
 * @method static array deleteObjects(array $keys, ?string $bucket = null)
 * @method static \Tigusigalpa\YandexCloudS3\Models\S3Object copyObject(string $sourceKey, string $destinationKey, ?string $sourceBucket = null, ?string $destinationBucket = null)
 * @method static string getObjectUrl(string $key, ?string $bucket = null, int $expiresIn = 3600)
 * @method static \Tigusigalpa\YandexCloudS3\Models\Bucket createBucket(string $bucketName, array $options = [])
 * @method static \Tigusigalpa\YandexCloudS3\Models\Bucket[] listBuckets()
 * @method static bool deleteBucket(?string $bucketName = null)
 * @method static void setBucket(string $bucket)
 * @method static string getBucket()
 * @method static \Tigusigalpa\YandexCloudS3\Auth\S3AuthManager getAuthManager()
 * @method static \Aws\S3\S3Client getAwsClient()
 * @method static void refreshToken()
 *
 * @see S3Client
 */
class YandexS3 extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yandexcloud-s3';
    }
}
