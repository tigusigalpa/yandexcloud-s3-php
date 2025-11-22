<?php

namespace Tigusigalpa\YandexCloudS3\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tigusigalpa\YandexCloudS3\Models\Bucket createBucket(string $bucketName, array $options = [])
 * @method static \Tigusigalpa\YandexCloudS3\Models\Bucket getBucket(string $bucketName)
 * @method static array listBuckets(?string $folderId = null)
 * @method static \Tigusigalpa\YandexCloudS3\Models\Bucket updateBucket(string $bucketName, array $updates)
 * @method static bool deleteBucket(string $bucketName)
 * @method static array setAccessBindings(string $bucketName, array $accessBindings)
 * @method static array updateAccessBindings(string $bucketName, array $accessBindingDeltas)
 * @method static array addRoleToBucket(string $bucketName, string $subjectId, string $roleId = 'storage.editor', string $subjectType = 'userAccount')
 * @method static array removeRoleFromBucket(string $bucketName, string $subjectId, string $roleId = 'storage.editor', string $subjectType = 'userAccount')
 * @method static array listAccessBindings(string $bucketName)
 * @method static \Tigusigalpa\YandexCloudS3\Auth\S3AuthManager getAuthManager()
 * @method static string getFolderId()
 * @method static void setFolderId(string $folderId)
 *
 * @see \Tigusigalpa\YandexCloudS3\BucketManagementClient
 */
class YandexBucketManagement extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yandexcloud-s3-bucket-management';
    }
}
