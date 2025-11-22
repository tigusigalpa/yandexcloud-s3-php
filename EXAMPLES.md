# Примеры использования Yandex Cloud S3 PHP SDK

## Содержание

- [Базовые операции с объектами](#базовые-операции-с-объектами)
- [Управление бакетами через S3 API](#управление-бакетами-через-s3-api)
- [Управление бакетами через REST API](#управление-бакетами-через-rest-api)
- [Управление ролями и доступом](#управление-ролями-и-доступом)
- [Работа с IAM и OAuth](#работа-с-iam-и-oauth)
- [Интеграция с Laravel](#интеграция-с-laravel)

## Базовые операции с объектами

### Загрузка файла

```php
use Tigusigalpa\YandexCloudS3\S3Client;

$s3 = new S3Client(
    oauthToken: 'your_oauth_token',
    bucket: 'my-bucket'
);

// Загрузка из файла
$object = $s3->putObject(
    key: 'documents/report.pdf',
    body: '/local/path/report.pdf',
    options: [
        'ContentType' => 'application/pdf',
        'Metadata' => [
            'author' => 'John Doe',
            'department' => 'Sales'
        ]
    ]
);

echo "Uploaded: {$object->key}, ETag: {$object->etag}";
```

### Загрузка из строки

```php
$content = "Hello, Yandex Cloud!";
$object = $s3->putObject(
    key: 'text/greeting.txt',
    body: $content,
    options: ['ContentType' => 'text/plain']
);
```

### Скачивание файла

```php
$content = $s3->getObject('documents/report.pdf');
file_put_contents('/local/path/downloaded.pdf', $content);
```

### Получение метаданных

```php
$metadata = $s3->getObjectMetadata('documents/report.pdf');
echo "Size: {$metadata->size} bytes\n";
echo "Type: {$metadata->contentType}\n";
echo "Modified: {$metadata->lastModified}\n";
```

### Проверка существования

```php
if ($s3->exists('documents/report.pdf')) {
    echo "File exists";
} else {
    echo "File not found";
}
```

### Список файлов

```php
$objects = $s3->listObjects('documents/', 100);
foreach ($objects as $object) {
    echo "{$object->key} - {$object->size} bytes\n";
}
```

### Удаление файла

```php
$s3->deleteObject('documents/old-report.pdf');
```

### Массовое удаление

```php
$result = $s3->deleteObjects([
    'documents/file1.pdf',
    'documents/file2.pdf',
    'documents/file3.pdf'
]);

echo "Deleted: " . count($result['deleted']) . "\n";
echo "Errors: " . count($result['errors']) . "\n";
```

### Копирование файла

```php
$s3->copyObject(
    sourceKey: 'documents/report.pdf',
    destinationKey: 'backup/report-2025.pdf'
);
```

### Получение подписанного URL

```php
// URL действителен 1 час
$url = $s3->getObjectUrl('documents/report.pdf', null, 3600);
echo "Download link: {$url}";
```

## Управление бакетами через S3 API

### Создание бакета

```php
$bucket = $s3->createBucket('new-bucket-name');
echo "Created: {$bucket->name}";
```

### Список бакетов

```php
$buckets = $s3->listBuckets();
foreach ($buckets as $bucket) {
    echo "{$bucket->name} - created at {$bucket->createdAt}\n";
}
```

### Удаление бакета

```php
$s3->deleteBucket('old-bucket-name');
```

## Управление бакетами через REST API

### Инициализация клиента

```php
use Tigusigalpa\YandexCloudS3\BucketManagementClient;

$bucketClient = new BucketManagementClient(
    oauthToken: 'your_oauth_token',
    folderId: 'your_folder_id'
);
```

### Создание бакета с параметрами

```php
$bucket = $bucketClient->createBucket('my-new-bucket', [
    'defaultStorageClass' => 'STANDARD',
    'maxSize' => 10737418240, // 10GB
    'anonymousAccessFlags' => [
        'read' => false,
        'list' => false
    ]
]);

echo "Created: {$bucket->name} in folder {$bucket->folderId}";
```

### Получение информации о бакете

```php
$bucket = $bucketClient->getBucket('my-bucket');
echo "Name: {$bucket->name}\n";
echo "Folder: {$bucket->folderId}\n";
echo "Created: {$bucket->createdAt}\n";
echo "Storage Class: {$bucket->defaultStorageClass}\n";
```

### Список бакетов в папке

```php
$buckets = $bucketClient->listBuckets();
foreach ($buckets as $bucket) {
    echo "{$bucket->name}\n";
}
```

### Обновление бакета

```php
$bucket = $bucketClient->updateBucket('my-bucket', [
    'maxSize' => 21474836480, // 20GB
]);
```

### Удаление бакета

```php
$bucketClient->deleteBucket('old-bucket');
```

## Управление ролями и доступом

### Добавление роли пользователю

```php
// Получить ID пользователя
$oauthManager = $bucketClient->getAuthManager()->getOAuthTokenManager();
$user = $oauthManager->getUserByLogin('username@example.com');
$userId = $user['id'];

// Добавить роль editor
$bucketClient->addRoleToBucket(
    bucketName: 'my-bucket',
    subjectId: $userId,
    roleId: 'storage.editor',
    subjectType: 'userAccount'
);

echo "Role 'storage.editor' added to user {$userId}";
```

### Удаление роли

```php
$bucketClient->removeRoleFromBucket(
    bucketName: 'my-bucket',
    subjectId: $userId,
    roleId: 'storage.editor'
);
```

### Список ролей бакета

```php
$bindings = $bucketClient->listAccessBindings('my-bucket');
foreach ($bindings as $binding) {
    echo "Role: {$binding['roleId']}\n";
    echo "Subject: {$binding['subject']['id']} ({$binding['subject']['type']})\n";
    echo "---\n";
}
```

### Установка всех ролей (перезапись)

```php
$bucketClient->setAccessBindings('my-bucket', [
    [
        'roleId' => 'storage.admin',
        'subject' => [
            'id' => 'admin-user-id',
            'type' => 'userAccount'
        ]
    ],
    [
        'roleId' => 'storage.viewer',
        'subject' => [
            'id' => 'viewer-user-id',
            'type' => 'userAccount'
        ]
    ]
]);
```

### Массовое обновление ролей

```php
$bucketClient->updateAccessBindings('my-bucket', [
    // Добавить роль
    [
        'action' => 'ADD',
        'accessBinding' => [
            'roleId' => 'storage.uploader',
            'subject' => [
                'id' => 'uploader-user-id',
                'type' => 'userAccount'
            ]
        ]
    ],
    // Удалить роль
    [
        'action' => 'REMOVE',
        'accessBinding' => [
            'roleId' => 'storage.viewer',
            'subject' => [
                'id' => 'old-viewer-id',
                'type' => 'userAccount'
            ]
        ]
    ]
]);
```

## Работа с IAM и OAuth

### Получение информации о пользователе

```php
$authManager = $s3->getAuthManager();
$oauthManager = $authManager->getOAuthTokenManager();

$user = $oauthManager->getUserByLogin('username@example.com');
echo "User ID: {$user['id']}\n";
echo "Login: {$user['login']}\n";
```

### Список облаков

```php
$clouds = $oauthManager->listClouds();
foreach ($clouds as $cloud) {
    echo "Cloud: {$cloud['name']} (ID: {$cloud['id']})\n";
}
```

### Список папок в облаке

```php
$cloudId = 'your-cloud-id';
$folders = $oauthManager->listFolders($cloudId);
foreach ($folders as $folder) {
    echo "Folder: {$folder['name']} (ID: {$folder['id']})\n";
}
```

### Получение и обновление IAM токена

```php
// Получить текущий токен (автоматически обновляется)
$token = $authManager->getValidIamToken();

// Принудительно получить новый токен
$newToken = $authManager->getIamToken();

// Обновить клиент с новым токеном
$s3->refreshToken();
```

## Интеграция с Laravel

### Использование через Facade

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexS3;
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexBucketManagement;

// Операции с файлами
YandexS3::putObject('uploads/photo.jpg', $request->file('photo')->getPathname());
$url = YandexS3::getObjectUrl('uploads/photo.jpg', null, 7200);

// Управление бакетами
$bucket = YandexBucketManagement::createBucket('user-uploads');
YandexBucketManagement::addRoleToBucket('user-uploads', $userId, 'storage.editor');
```

### Использование через Dependency Injection

```php
use Tigusigalpa\YandexCloudS3\S3Client;
use Tigusigalpa\YandexCloudS3\BucketManagementClient;

class FileController extends Controller
{
    public function upload(Request $request, S3Client $s3)
    {
        $file = $request->file('document');
        
        $object = $s3->putObject(
            key: 'documents/' . $file->getClientOriginalName(),
            body: $file->getPathname(),
            options: [
                'ContentType' => $file->getMimeType(),
            ]
        );
        
        return response()->json([
            'success' => true,
            'key' => $object->key,
            'url' => $s3->getObjectUrl($object->key)
        ]);
    }
    
    public function download(string $filename, S3Client $s3)
    {
        $content = $s3->getObject('documents/' . $filename);
        
        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
```

### Пример сервиса для работы с файлами

```php
namespace App\Services;

use Tigusigalpa\YandexCloudS3\S3Client;
use Illuminate\Http\UploadedFile;

class FileStorageService
{
    public function __construct(private S3Client $s3)
    {
    }
    
    public function uploadUserFile(int $userId, UploadedFile $file): string
    {
        $key = "users/{$userId}/" . time() . '-' . $file->getClientOriginalName();
        
        $this->s3->putObject(
            key: $key,
            body: $file->getPathname(),
            options: [
                'ContentType' => $file->getMimeType(),
                'Metadata' => [
                    'user_id' => (string)$userId,
                    'original_name' => $file->getClientOriginalName(),
                ]
            ]
        );
        
        return $key;
    }
    
    public function getFileUrl(string $key, int $expiresIn = 3600): string
    {
        return $this->s3->getObjectUrl($key, null, $expiresIn);
    }
    
    public function deleteUserFiles(int $userId): void
    {
        $objects = $this->s3->listObjects("users/{$userId}/");
        $keys = array_map(fn($obj) => $obj->key, $objects);
        
        if (!empty($keys)) {
            $this->s3->deleteObjects($keys);
        }
    }
}
```

### Обработка ошибок в Laravel

```php
use Tigusigalpa\YandexCloudS3\Exceptions\ApiException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;

try {
    YandexS3::putObject('documents/file.pdf', $filePath);
} catch (AuthenticationException $e) {
    Log::error('S3 Authentication failed', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Storage authentication failed'], 500);
} catch (ApiException $e) {
    Log::error('S3 API error', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Failed to upload file'], 500);
}
```

## Дополнительные примеры

### Работа с несколькими бакетами

```php
$s3 = new S3Client(
    oauthToken: 'token',
    bucket: 'default-bucket'
);

// Работа с default-bucket
$s3->putObject('file.txt', 'content');

// Переключение на другой бакет
$s3->setBucket('another-bucket');
$s3->putObject('file.txt', 'content');

// Или указать бакет явно
$s3->putObject('file.txt', 'content', [], 'specific-bucket');
```

### Копирование между бакетами

```php
$s3->copyObject(
    sourceKey: 'documents/report.pdf',
    destinationKey: 'reports/2025/report.pdf',
    sourceBucket: 'source-bucket',
    destinationBucket: 'destination-bucket'
);
```

### Прямая работа с AWS SDK

```php
$awsClient = $s3->getAwsClient();

// Использование любых методов AWS SDK
$result = $awsClient->listObjectsV2([
    'Bucket' => 'my-bucket',
    'Prefix' => 'documents/',
    'MaxKeys' => 1000,
]);

foreach ($result['Contents'] as $object) {
    echo $object['Key'] . "\n";
}
```
