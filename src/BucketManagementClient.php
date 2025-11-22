<?php

namespace Tigusigalpa\YandexCloudS3;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\YandexCloudS3\Auth\S3AuthManager;
use Tigusigalpa\YandexCloudS3\Exceptions\ApiException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudS3\Models\Bucket;

/**
 * REST API client for Yandex Cloud Object Storage bucket management
 * 
 * Provides access to bucket operations via Yandex Cloud REST API
 * including role management and access control
 */
class BucketManagementClient
{
    private const BUCKET_ENDPOINT = 'https://storage.api.cloud.yandex.net/storage/v1/buckets';
    
    private Client $httpClient;
    private S3AuthManager $authManager;
    private string $folderId;

    /**
     * Initialize Bucket Management Client
     *
     * @param string $oauthToken Yandex Cloud OAuth token
     * @param string $folderId Folder ID where buckets are located
     * @param Client|null $httpClient Custom HTTP client (optional)
     * @throws AuthenticationException
     */
    public function __construct(
        string $oauthToken,
        string $folderId,
        ?Client $httpClient = null
    ) {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        if (empty($folderId)) {
            throw new AuthenticationException('Folder ID cannot be empty');
        }

        $this->authManager = new S3AuthManager($oauthToken);
        $this->folderId = $folderId;
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Create bucket via REST API
     *
     * @param string $bucketName Bucket name
     * @param array $options Additional options (acl, defaultStorageClass, etc.)
     * @return Bucket
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/create
     */
    public function createBucket(string $bucketName, array $options = []): Bucket
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $requestData = array_merge([
                'name' => $bucketName,
                'folderId' => $this->folderId,
            ], $options);

            $response = $this->httpClient->post(self::BUCKET_ENDPOINT, [
                'json' => $requestData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return new Bucket([
                'name' => $data['name'] ?? $bucketName,
                'folder_id' => $data['folderId'] ?? $this->folderId,
                'created_at' => $data['createdAt'] ?? null,
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Error creating bucket: ' . $e->getMessage());
        }
    }

    /**
     * Get bucket information via REST API
     *
     * @param string $bucketName Bucket name
     * @return Bucket
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/get
     */
    public function getBucket(string $bucketName): Bucket
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $response = $this->httpClient->get(self::BUCKET_ENDPOINT . '/' . $bucketName, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return new Bucket([
                'name' => $data['name'] ?? $bucketName,
                'folder_id' => $data['folderId'] ?? null,
                'created_at' => $data['createdAt'] ?? null,
                'default_storage_class' => $data['defaultStorageClass'] ?? null,
                'max_size' => $data['maxSize'] ?? null,
                'anonymous_access_flags' => $data['anonymousAccessFlags'] ?? null,
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Error getting bucket: ' . $e->getMessage());
        }
    }

    /**
     * List buckets in folder via REST API
     *
     * @param string|null $folderId Folder ID (uses default if not provided)
     * @return array Array of Bucket instances
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/list
     */
    public function listBuckets(?string $folderId = null): array
    {
        $iamToken = $this->authManager->getValidIamToken();
        $folderId = $folderId ?? $this->folderId;

        try {
            $response = $this->httpClient->get(self::BUCKET_ENDPOINT, [
                'query' => [
                    'folderId' => $folderId,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $buckets = [];
            if (isset($data['buckets'])) {
                foreach ($data['buckets'] as $item) {
                    $buckets[] = new Bucket([
                        'name' => $item['name'] ?? '',
                        'folder_id' => $item['folderId'] ?? null,
                        'created_at' => $item['createdAt'] ?? null,
                    ]);
                }
            }

            return $buckets;
        } catch (GuzzleException $e) {
            throw new ApiException('Error listing buckets: ' . $e->getMessage());
        }
    }

    /**
     * Update bucket settings via REST API
     *
     * @param string $bucketName Bucket name
     * @param array $updates Update parameters
     * @return Bucket
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/update
     */
    public function updateBucket(string $bucketName, array $updates): Bucket
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $response = $this->httpClient->patch(self::BUCKET_ENDPOINT . '/' . $bucketName, [
                'json' => $updates,
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return new Bucket([
                'name' => $data['name'] ?? $bucketName,
                'folder_id' => $data['folderId'] ?? null,
                'created_at' => $data['createdAt'] ?? null,
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Error updating bucket: ' . $e->getMessage());
        }
    }

    /**
     * Delete bucket via REST API
     *
     * @param string $bucketName Bucket name
     * @return bool
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/delete
     */
    public function deleteBucket(string $bucketName): bool
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $this->httpClient->delete(self::BUCKET_ENDPOINT . '/' . $bucketName, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                ],
            ]);

            return true;
        } catch (GuzzleException $e) {
            throw new ApiException('Error deleting bucket: ' . $e->getMessage());
        }
    }

    /**
     * Set access bindings (roles) for bucket
     *
     * @param string $bucketName Bucket name
     * @param array $accessBindings Array of access bindings
     * @return array Operation result
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/setAccessBindings
     * 
     * Example:
     * $accessBindings = [
     *     [
     *         'roleId' => 'storage.editor',
     *         'subject' => [
     *             'id' => 'userAccountId',
     *             'type' => 'userAccount'
     *         ]
     *     ]
     * ]
     */
    public function setAccessBindings(string $bucketName, array $accessBindings): array
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $response = $this->httpClient->post(
                self::BUCKET_ENDPOINT . '/' . $bucketName . ':setAccessBindings',
                [
                    'json' => [
                        'accessBindings' => $accessBindings,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $iamToken,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new ApiException('Error setting access bindings: ' . $e->getMessage());
        }
    }

    /**
     * Update access bindings (roles) for bucket
     *
     * @param string $bucketName Bucket name
     * @param array $accessBindingDeltas Array of access binding deltas (ADD/REMOVE)
     * @return array Operation result
     * @throws ApiException
     * @throws AuthenticationException
     * @see https://yandex.cloud/ru/docs/storage/api-ref/Bucket/updateAccessBindings
     * 
     * Example:
     * $deltas = [
     *     [
     *         'action' => 'ADD', // or 'REMOVE'
     *         'accessBinding' => [
     *             'roleId' => 'storage.editor',
     *             'subject' => [
     *                 'id' => 'userAccountId',
     *                 'type' => 'userAccount'
     *             ]
     *         ]
     *     ]
     * ]
     */
    public function updateAccessBindings(string $bucketName, array $accessBindingDeltas): array
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $response = $this->httpClient->post(
                self::BUCKET_ENDPOINT . '/' . $bucketName . ':updateAccessBindings',
                [
                    'json' => [
                        'accessBindingDeltas' => $accessBindingDeltas,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $iamToken,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new ApiException('Error updating access bindings: ' . $e->getMessage());
        }
    }

    /**
     * Add role to bucket (convenience method)
     *
     * @param string $bucketName Bucket name
     * @param string $subjectId Subject ID (user or service account)
     * @param string $roleId Role ID (e.g., 'storage.editor', 'storage.viewer')
     * @param string $subjectType Subject type: 'userAccount' or 'serviceAccount'
     * @return array Operation result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function addRoleToBucket(
        string $bucketName,
        string $subjectId,
        string $roleId = 'storage.editor',
        string $subjectType = 'userAccount'
    ): array {
        return $this->updateAccessBindings($bucketName, [
            [
                'action' => 'ADD',
                'accessBinding' => [
                    'roleId' => $roleId,
                    'subject' => [
                        'id' => $subjectId,
                        'type' => $subjectType,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Remove role from bucket (convenience method)
     *
     * @param string $bucketName Bucket name
     * @param string $subjectId Subject ID (user or service account)
     * @param string $roleId Role ID (e.g., 'storage.editor', 'storage.viewer')
     * @param string $subjectType Subject type: 'userAccount' or 'serviceAccount'
     * @return array Operation result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function removeRoleFromBucket(
        string $bucketName,
        string $subjectId,
        string $roleId = 'storage.editor',
        string $subjectType = 'userAccount'
    ): array {
        return $this->updateAccessBindings($bucketName, [
            [
                'action' => 'REMOVE',
                'accessBinding' => [
                    'roleId' => $roleId,
                    'subject' => [
                        'id' => $subjectId,
                        'type' => $subjectType,
                    ],
                ],
            ],
        ]);
    }

    /**
     * List access bindings for bucket
     *
     * @param string $bucketName Bucket name
     * @return array Access bindings
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function listAccessBindings(string $bucketName): array
    {
        $iamToken = $this->authManager->getValidIamToken();

        try {
            $response = $this->httpClient->get(
                self::BUCKET_ENDPOINT . '/' . $bucketName . ':listAccessBindings',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $iamToken,
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['accessBindings'] ?? [];
        } catch (GuzzleException $e) {
            throw new ApiException('Error listing access bindings: ' . $e->getMessage());
        }
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
     * Get folder ID
     *
     * @return string
     */
    public function getFolderId(): string
    {
        return $this->folderId;
    }

    /**
     * Set folder ID
     *
     * @param string $folderId Folder ID
     * @return void
     */
    public function setFolderId(string $folderId): void
    {
        $this->folderId = $folderId;
    }
}
