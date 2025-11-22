# Yandex Cloud S3 PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://www.php.net/)
[![Laravel: 8-12](https://img.shields.io/badge/Laravel-8--12-blue.svg)](https://laravel.com/)

Full-featured PHP SDK for integration with [Yandex Cloud Object Storage](https://yandex.cloud/en/services/storage) with support for Laravel 8-12. The library is compatible with AWS SDK and uses the S3-compatible API of Yandex Cloud.

[Русская версия](README.md)

## Features

- ✅ **S3-compatible API** - full support for object and bucket operations
- ✅ **AWS SDK for PHP** - uses the proven AWS SDK v3
- ✅ **Laravel 8-12 Support** - built-in integration with Service Provider and Facade
- ✅ **Bucket Management** - create, delete, list buckets via S3 and REST API
- ✅ **Object Operations** - upload, download, delete, copy objects
- ✅ **Presigned URLs** - generate signed URLs for direct access
- ✅ **IAM Tokens** - automatic token management and refresh
- ✅ **OAuth Integration** - get IAM tokens via OAuth
- ✅ **Access Management** - support for IAM roles and bucket permissions
- ✅ **REST API for Buckets** - full bucket management via Yandex Cloud REST API
- ✅ **Role Management** - assign and remove roles for buckets

## Installation

### Via Composer

```bash
composer require tigusigalpa/yandexcloud-s3-php
```

### Requirements

- PHP >= 8.0
- Laravel >= 8.0 (optional)
- AWS SDK for PHP >= 3.200
- Guzzle HTTP >= 7.0

## Quick Start

### 1. Obtain OAuth Token

Get OAuth token from:
https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb

### 2. Configuration for Laravel

Publish configuration:

```bash
php artisan vendor:publish --tag=yandexcloud-s3-config
```

Add variables to `.env`:

```env
YANDEX_CLOUD_OAUTH_TOKEN=your_oauth_token_here
YANDEX_CLOUD_BUCKET=your-bucket-name
YANDEX_CLOUD_FOLDER_ID=your-folder-id
YANDEX_CLOUD_ENDPOINT=https://storage.yandexcloud.net
```

### 3. Usage in Laravel

#### Via Facade:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexS3;

// Upload file
YandexS3::putObject('documents/file.pdf', '/local/path/file.pdf', [
    'ContentType' => 'application/pdf',
]);

// Download file
$content = YandexS3::getObject('documents/file.pdf');

// List objects
$objects = YandexS3::listObjects('documents/');

// Delete file
YandexS3::deleteObject('documents/file.pdf');

// Get signed URL
$url = YandexS3::getObjectUrl('documents/file.pdf', null, 3600);

// Copy object
YandexS3::copyObject('source/file.pdf', 'destination/file.pdf');

// Get object metadata
$metadata = YandexS3::getObjectMetadata('documents/file.pdf');
echo $metadata->size;
echo $metadata->lastModified;
```

#### Via Service Container:

```php
use Tigusigalpa\YandexCloudS3\S3Client;

public function upload(S3Client $s3)
{
    $s3->putObject('documents/file.pdf', '/local/path/file.pdf');
}
```

## Usage Without Laravel

### Initialization:

```php
use Tigusigalpa\YandexCloudS3\S3Client;

$s3 = new S3Client(
    oauthToken: 'your_oauth_token',
    bucket: 'your-bucket-name',
    endpoint: 'https://storage.yandexcloud.net', // optional
    options: [] // optional AWS SDK parameters
);
```

### Operation Examples:

```php
// List objects
$objects = $s3->listObjects('prefix/', 100);
foreach ($objects as $object) {
    echo $object->key;
    echo $object->size;
    echo $object->lastModified;
}

// Check existence
if ($s3->exists('documents/file.pdf')) {
    echo 'File exists';
}

// Upload
$object = $s3->putObject(
    key: 'documents/file.pdf',
    body: '/local/path/file.pdf', // file path or content
    options: [
        'ContentType' => 'application/pdf',
        'Metadata' => ['custom-key' => 'custom-value'],
    ]
);
echo $object->etag;

// Download
$content = $s3->getObject('documents/file.pdf');
file_put_contents('/local/path/downloaded.pdf', $content);

// Metadata
$metadata = $s3->getObjectMetadata('documents/file.pdf');
echo $metadata->size;
echo $metadata->contentType;

// Delete
$s3->deleteObject('documents/file.pdf');

// Batch delete
$result = $s3->deleteObjects([
    'documents/file1.pdf',
    'documents/file2.pdf',
    'documents/file3.pdf',
]);
echo count($result['deleted']);

// Copy
$s3->copyObject(
    sourceKey: 'source/file.pdf',
    destinationKey: 'backup/file.pdf'
);

// Presigned URL
$url = $s3->getObjectUrl('documents/file.pdf', null, 3600); // 1 hour

// Switch bucket
$s3->setBucket('another-bucket');
$currentBucket = $s3->getBucket();
```

## Bucket Operations

```php
// Create bucket
$s3->createBucket('new-bucket-name');

// List all buckets
$buckets = $s3->listBuckets();
foreach ($buckets as $bucket) {
    echo $bucket->name;
    echo $bucket->createdAt;
}

// Delete bucket (must be empty)
$s3->deleteBucket('bucket-to-delete');

// Delete with default bucket name
$s3->deleteBucket();
```

## REST API Bucket Management

For advanced bucket management use `BucketManagementClient`:

### In Laravel via Facade:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexBucketManagement;

// Create bucket via REST API
$bucket = YandexBucketManagement::createBucket('new-bucket', [
    'defaultStorageClass' => 'STANDARD',
]);

// Get bucket information
$bucket = YandexBucketManagement::getBucket('my-bucket');
echo $bucket->name;
echo $bucket->folderId;
echo $bucket->createdAt;

// List buckets in folder
$buckets = YandexBucketManagement::listBuckets();
foreach ($buckets as $bucket) {
    echo $bucket->name;
}

// Update bucket
$bucket = YandexBucketManagement::updateBucket('my-bucket', [
    'maxSize' => 1073741824, // 1GB
]);

// Delete bucket
YandexBucketManagement::deleteBucket('old-bucket');
```

### Without Laravel:

```php
use Tigusigalpa\YandexCloudS3\BucketManagementClient;

$bucketClient = new BucketManagementClient(
    oauthToken: 'your_oauth_token',
    folderId: 'your_folder_id'
);

$bucket = $bucketClient->createBucket('new-bucket');
```

## Bucket Role and Access Management

### Assigning Roles:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexBucketManagement;

// Add role to user
YandexBucketManagement::addRoleToBucket(
    bucketName: 'my-bucket',
    subjectId: 'user-account-id',
    roleId: 'storage.editor', // or 'storage.viewer', 'storage.admin'
    subjectType: 'userAccount' // or 'serviceAccount'
);

// Remove role
YandexBucketManagement::removeRoleFromBucket(
    bucketName: 'my-bucket',
    subjectId: 'user-account-id',
    roleId: 'storage.editor'
);

// List bucket roles
$bindings = YandexBucketManagement::listAccessBindings('my-bucket');
foreach ($bindings as $binding) {
    echo $binding['roleId'];
    echo $binding['subject']['id'];
}
```

### Advanced Role Management:

```php
// Set roles (overwrites all existing)
YandexBucketManagement::setAccessBindings('my-bucket', [
    [
        'roleId' => 'storage.editor',
        'subject' => [
            'id' => 'user-id-1',
            'type' => 'userAccount'
        ]
    ],
    [
        'roleId' => 'storage.viewer',
        'subject' => [
            'id' => 'user-id-2',
            'type' => 'userAccount'
        ]
    ]
]);

// Update roles (add/remove)
YandexBucketManagement::updateAccessBindings('my-bucket', [
    [
        'action' => 'ADD',
        'accessBinding' => [
            'roleId' => 'storage.admin',
            'subject' => [
                'id' => 'user-id-3',
                'type' => 'userAccount'
            ]
        ]
    ],
    [
        'action' => 'REMOVE',
        'accessBinding' => [
            'roleId' => 'storage.viewer',
            'subject' => [
                'id' => 'user-id-2',
                'type' => 'userAccount'
            ]
        ]
    ]
]);
```

### Available Object Storage Roles:

- `storage.admin` - full bucket access
- `storage.editor` - read and write objects
- `storage.viewer` - read-only access
- `storage.uploader` - upload objects only
- `storage.configViewer` - view configuration
- `storage.configurer` - manage configuration

## Access Management and IAM

```php
use Tigusigalpa\YandexCloudS3\Auth\S3AuthManager;

$authManager = $s3->getAuthManager();
$oauthManager = $authManager->getOAuthTokenManager();

// Get user information
$user = $oauthManager->getUserByLogin('username');
echo $user['id']; // Subject ID for use in roles

// Get clouds
$clouds = $oauthManager->listClouds();
foreach ($clouds as $cloud) {
    echo $cloud['id'];
    echo $cloud['name'];
}

// Get folders
$folders = $oauthManager->listFolders($cloudId);
foreach ($folders as $folder) {
    echo $folder['id'];
    echo $folder['name'];
}
```

## Token Management

```php
// Get current IAM token (auto-refreshes)
$token = $s3->getAuthManager()->getValidIamToken();

// Get new IAM token
$newToken = $s3->getAuthManager()->getIamToken();

// Reinitialize client (refresh tokens)
$s3->refreshToken();
```

## Passing AWS SDK Options

```php
// On initialization
$s3 = new S3Client(
    oauthToken: 'token',
    bucket: 'bucket',
    options: [
        'use_path_style_endpoint' => true,
        'signature_version' => 'v4',
    ]
);

// Or get AWS client directly
$awsClient = $s3->getAwsClient();
$result = $awsClient->getObject([
    'Bucket' => 'my-bucket',
    'Key' => 'my-key',
]);
```

## Error Handling

```php
use Tigusigalpa\YandexCloudS3\Exceptions\{
    YandexCloudS3Exception,
    ApiException,
    AuthenticationException,
};

try {
    $s3->getObject('non-existent-file.pdf');
} catch (ApiException $e) {
    echo 'API error: ' . $e->getMessage();
} catch (AuthenticationException $e) {
    echo 'Authentication error: ' . $e->getMessage();
} catch (YandexCloudS3Exception $e) {
    echo 'General error: ' . $e->getMessage();
}
```

## Laravel Configuration

File `config/yandexcloud-s3.php`:

```php
return [
    // Yandex Cloud OAuth token
    'oauth_token' => env('YANDEX_CLOUD_OAUTH_TOKEN'),
    
    // Default bucket
    'bucket' => env('YANDEX_CLOUD_BUCKET'),
    
    // Folder ID for REST API bucket management
    'folder_id' => env('YANDEX_CLOUD_FOLDER_ID'),
    
    // Endpoint
    'endpoint' => env('YANDEX_CLOUD_ENDPOINT', 'https://storage.yandexcloud.net'),
    
    // Region
    'region' => env('YANDEX_CLOUD_REGION', 'ru-central1'),
    
    // AWS SDK options
    'options' => [
        'http' => [
            'timeout' => 30,
            'connect_timeout' => 10,
        ],
    ],
];
```

## Usage Examples

### Upload File via HTTP

```php
public function uploadFile(Request $request)
{
    $file = $request->file('document');
    
    $object = YandexS3::putObject(
        key: 'uploads/' . $file->getClientOriginalName(),
        body: $file->getPathname(),
        options: [
            'ContentType' => $file->getMimeType(),
        ]
    );
    
    return response()->json([
        'url' => YandexS3::getObjectUrl($object->key),
    ]);
}
```

### List Files

```php
public function listFiles()
{
    $objects = YandexS3::listObjects('documents/', 50);
    
    return view('files.list', [
        'files' => array_map(fn($obj) => [
            'name' => basename($obj->key),
            'size' => $obj->size,
            'url' => YandexS3::getObjectUrl($obj->key),
        ], $objects),
    ]);
}
```

### Download File

```php
public function downloadFile($filename)
{
    $content = YandexS3::getObject('documents/' . $filename);
    
    return response($content, 200)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', "attachment; filename='$filename'");
}
```

## Documentation and Links

- [Yandex Cloud Object Storage](https://yandex.cloud/en/services/storage)
- [Object Storage Documentation](https://yandex.cloud/en/docs/storage/quickstart/)
- [REST API Documentation](https://yandex.cloud/en/docs/storage/api-ref/Bucket/)
- [S3 API Documentation](https://yandex.cloud/en/docs/storage/s3/api-ref/object)
- [AWS SDK for PHP](https://docs.aws.amazon.com/sdk-for-php/)
- [Access Management](https://yandex.cloud/en/docs/storage/security/)

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

Igor Sazonov - [GitHub](https://github.com/tigusigalpa)

## Acknowledgments

Thanks to Yandex Cloud for providing the S3-compatible API and AWS for the excellent PHP SDK.
