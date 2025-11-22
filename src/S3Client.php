<?php

namespace Tigusigalpa\YandexCloudS3;

use Aws\S3\S3Client as AwsS3Client;
use Aws\Exception\AwsException;
use Tigusigalpa\YandexCloudS3\Auth\S3AuthManager;
use Tigusigalpa\YandexCloudS3\Exceptions\ApiException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudS3\Models\Bucket;
use Tigusigalpa\YandexCloudS3\Models\S3Object;

/**
 * Main S3Client for Yandex Cloud Object Storage
 * 
 * Compatible with AWS SDK for PHP 3.x
 * Supports all standard S3 operations
 */
class S3Client
{
    private const DEFAULT_ENDPOINT = 'https://storage.yandexcloud.net';
    private const REGION = 'ru-central1';

    private AwsS3Client $awsClient;
    private S3AuthManager $authManager;
    private string $endpoint;
    private string $region;
    private string $bucket;
    private ?string $serviceAccountId = null;
    private array $options;

    /**
     * Initialize S3Client
     *
     * @param string $oauthToken Yandex Cloud OAuth token
     * @param string $bucket Default bucket name
     * @param string|null $endpoint Custom endpoint (optional)
     * @param array $options Additional AWS SDK options
     * @throws AuthenticationException
     */
    public function __construct(
        string $oauthToken,
        string $bucket,
        ?string $endpoint = null,
        array $options = []
    ) {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        if (empty($bucket)) {
            throw new AuthenticationException('Bucket name cannot be empty');
        }

        $this->authManager = new S3AuthManager($oauthToken);
        $this->endpoint = $endpoint ?? self::DEFAULT_ENDPOINT;
        $this->region = self::REGION;
        $this->bucket = $bucket;
        $this->options = $options;

        $this->initializeAwsClient();
    }

    /**
     * Initialize AWS S3 client with Yandex Cloud credentials
     *
     * @throws AuthenticationException
     */
    private function initializeAwsClient(): void
    {
        try {
            $iamToken = $this->authManager->getValidIamToken();

            $this->awsClient = new AwsS3Client([
                'version' => 'latest',
                'region' => $this->region,
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $iamToken,
                    'secret' => '', // Yandex Cloud uses IAM token as key, empty secret
                ],
                ...($this->options),
            ]);
        } catch (AuthenticationException $e) {
            throw new AuthenticationException('Failed to initialize S3 client: ' . $e->getMessage());
        }
    }

    /**
     * List all objects in bucket
     *
     * @param string|null $prefix Filter by key prefix
     * @param int $maxKeys Maximum number of objects to return (default 1000)
     * @return array Array of S3Object instances
     * @throws ApiException
     */
    public function listObjects(?string $prefix = null, int $maxKeys = 1000): array
    {
        try {
            $params = [
                'Bucket' => $this->bucket,
                'MaxKeys' => $maxKeys,
            ];

            if ($prefix !== null) {
                $params['Prefix'] = $prefix;
            }

            $result = $this->awsClient->listObjectsV2($params);

            $objects = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $item) {
                    $objects[] = new S3Object([
                        'key' => $item['Key'],
                        'bucket' => $this->bucket,
                        'etag' => $item['ETag'] ?? null,
                        'size' => $item['Size'] ?? null,
                        'last_modified' => $item['LastModified'] ?? null,
                        'storage_class' => $item['StorageClass'] ?? null,
                    ]);
                }
            }

            return $objects;
        } catch (AwsException $e) {
            throw new ApiException('Error listing objects: ' . $e->getMessage());
        }
    }

    /**
     * Check if object exists in bucket
     *
     * @param string $key Object key
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return bool
     * @throws ApiException
     */
    public function exists(string $key, ?string $bucket = null): bool
    {
        try {
            return $this->awsClient->doesObjectExist(
                $bucket ?? $this->bucket,
                $key
            );
        } catch (AwsException $e) {
            throw new ApiException('Error checking object existence: ' . $e->getMessage());
        }
    }

    /**
     * Get object metadata
     *
     * @param string $key Object key
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return S3Object
     * @throws ApiException
     */
    public function getObjectMetadata(string $key, ?string $bucket = null): S3Object
    {
        try {
            $result = $this->awsClient->headObject([
                'Bucket' => $bucket ?? $this->bucket,
                'Key' => $key,
            ]);

            return new S3Object([
                'key' => $key,
                'bucket' => $bucket ?? $this->bucket,
                'etag' => $result['ETag'] ?? null,
                'size' => $result['ContentLength'] ?? null,
                'last_modified' => $result['LastModified'] ?? null,
                'storage_class' => $result['StorageClass'] ?? null,
                'content_type' => $result['ContentType'] ?? null,
                'metadata' => $result['Metadata'] ?? null,
            ]);
        } catch (AwsException $e) {
            throw new ApiException('Error getting object metadata: ' . $e->getMessage());
        }
    }

    /**
     * Get object content
     *
     * @param string $key Object key
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return string Object content
     * @throws ApiException
     */
    public function getObject(string $key, ?string $bucket = null): string
    {
        try {
            $result = $this->awsClient->getObject([
                'Bucket' => $bucket ?? $this->bucket,
                'Key' => $key,
            ]);

            return (string)$result['Body'];
        } catch (AwsException $e) {
            throw new ApiException('Error getting object: ' . $e->getMessage());
        }
    }

    /**
     * Upload object to bucket
     *
     * @param string $key Object key
     * @param string|resource $body Object content or file path
     * @param array $options Additional upload options (ContentType, Metadata, etc.)
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return S3Object
     * @throws ApiException
     */
    public function putObject(
        string $key,
        $body,
        array $options = [],
        ?string $bucket = null
    ): S3Object {
        try {
            // If body is a file path, read it
            if (is_string($body) && file_exists($body)) {
                $body = fopen($body, 'r');
            }

            $params = array_merge([
                'Bucket' => $bucket ?? $this->bucket,
                'Key' => $key,
                'Body' => $body,
            ], $options);

            $result = $this->awsClient->putObject($params);

            return new S3Object([
                'key' => $key,
                'bucket' => $bucket ?? $this->bucket,
                'etag' => $result['ETag'] ?? null,
                'version_id' => $result['VersionId'] ?? null,
            ]);
        } catch (AwsException $e) {
            throw new ApiException('Error uploading object: ' . $e->getMessage());
        }
    }

    /**
     * Delete object from bucket
     *
     * @param string $key Object key
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return bool
     * @throws ApiException
     */
    public function deleteObject(string $key, ?string $bucket = null): bool
    {
        try {
            $this->awsClient->deleteObject([
                'Bucket' => $bucket ?? $this->bucket,
                'Key' => $key,
            ]);

            return true;
        } catch (AwsException $e) {
            throw new ApiException('Error deleting object: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple objects from bucket
     *
     * @param array $keys Array of object keys
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @return array Deleted and failed keys
     * @throws ApiException
     */
    public function deleteObjects(array $keys, ?string $bucket = null): array
    {
        try {
            $objects = array_map(fn($key) => ['Key' => $key], $keys);

            $result = $this->awsClient->deleteObjects([
                'Bucket' => $bucket ?? $this->bucket,
                'Delete' => [
                    'Objects' => $objects,
                ],
            ]);

            return [
                'deleted' => $result['Deleted'] ?? [],
                'errors' => $result['Errors'] ?? [],
            ];
        } catch (AwsException $e) {
            throw new ApiException('Error deleting multiple objects: ' . $e->getMessage());
        }
    }

    /**
     * Copy object within or between buckets
     *
     * @param string $sourceKey Source object key
     * @param string $destinationKey Destination object key
     * @param string|null $sourceBucket Source bucket (uses default if not provided)
     * @param string|null $destinationBucket Destination bucket (uses default if not provided)
     * @return S3Object
     * @throws ApiException
     */
    public function copyObject(
        string $sourceKey,
        string $destinationKey,
        ?string $sourceBucket = null,
        ?string $destinationBucket = null
    ): S3Object {
        try {
            $sourceBucket = $sourceBucket ?? $this->bucket;
            $destinationBucket = $destinationBucket ?? $this->bucket;

            $result = $this->awsClient->copyObject([
                'Bucket' => $destinationBucket,
                'CopySource' => "{$sourceBucket}/{$sourceKey}",
                'Key' => $destinationKey,
            ]);

            return new S3Object([
                'key' => $destinationKey,
                'bucket' => $destinationBucket,
                'etag' => $result['CopyObjectResult']['ETag'] ?? null,
            ]);
        } catch (AwsException $e) {
            throw new ApiException('Error copying object: ' . $e->getMessage());
        }
    }

    /**
     * Get object URL for direct access
     *
     * @param string $key Object key
     * @param string|null $bucket Bucket name (uses default if not provided)
     * @param int $expiresIn Expiration time in seconds (default 3600 = 1 hour)
     * @return string Signed URL
     * @throws ApiException
     */
    public function getObjectUrl(
        string $key,
        ?string $bucket = null,
        int $expiresIn = 3600
    ): string {
        try {
            $cmd = $this->awsClient->getCommand('GetObject', [
                'Bucket' => $bucket ?? $this->bucket,
                'Key' => $key,
            ]);

            $request = $this->awsClient->createPresignedRequest($cmd, "+{$expiresIn} seconds");
            return (string)$request->getUri();
        } catch (AwsException $e) {
            throw new ApiException('Error generating presigned URL: ' . $e->getMessage());
        }
    }

    /**
     * Create bucket
     *
     * @param string $bucketName Bucket name
     * @param array $options Additional options
     * @return Bucket
     * @throws ApiException
     */
    public function createBucket(string $bucketName, array $options = []): Bucket
    {
        try {
            $params = array_merge([
                'Bucket' => $bucketName,
            ], $options);

            $result = $this->awsClient->createBucket($params);

            return new Bucket([
                'name' => $bucketName,
            ]);
        } catch (AwsException $e) {
            throw new ApiException('Error creating bucket: ' . $e->getMessage());
        }
    }

    /**
     * List all buckets
     *
     * @return array Array of Bucket instances
     * @throws ApiException
     */
    public function listBuckets(): array
    {
        try {
            $result = $this->awsClient->listBuckets();

            $buckets = [];
            if (isset($result['Buckets'])) {
                foreach ($result['Buckets'] as $item) {
                    $buckets[] = new Bucket([
                        'name' => $item['Name'] ?? '',
                        'created_at' => $item['CreationDate'] ?? null,
                    ]);
                }
            }

            return $buckets;
        } catch (AwsException $e) {
            throw new ApiException('Error listing buckets: ' . $e->getMessage());
        }
    }

    /**
     * Delete bucket (must be empty)
     *
     * @param string|null $bucketName Bucket name (uses default if not provided)
     * @return bool
     * @throws ApiException
     */
    public function deleteBucket(?string $bucketName = null): bool
    {
        try {
            $this->awsClient->deleteBucket([
                'Bucket' => $bucketName ?? $this->bucket,
            ]);

            return true;
        } catch (AwsException $e) {
            throw new ApiException('Error deleting bucket: ' . $e->getMessage());
        }
    }

    /**
     * Set default bucket
     *
     * @param string $bucket Bucket name
     * @return void
     */
    public function setBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }

    /**
     * Get default bucket
     *
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * Get authentication manager
     *
     * @return S3AuthManager
     */
    public function getAuthManager(): S3AuthManager
    {
        return $this->authManager;
    }

    /**
     * Get AWS S3 client for advanced operations
     *
     * @return AwsS3Client
     */
    public function getAwsClient(): AwsS3Client
    {
        return $this->awsClient;
    }

    /**
     * Refresh IAM token
     *
     * @return void
     * @throws AuthenticationException
     */
    public function refreshToken(): void
    {
        $this->initializeAwsClient();
    }
}
