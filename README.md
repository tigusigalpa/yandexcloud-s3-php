# Yandex Cloud S3 PHP SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://www.php.net/)
[![Laravel: 8-12](https://img.shields.io/badge/Laravel-8--12-blue.svg)](https://laravel.com/)

Полнофункциональный PHP SDK для интеграции с [Yandex Cloud Object Storage](https://yandex.cloud/ru/services/storage) с поддержкой Laravel 8-12. Библиотека совместима с AWS SDK и использует S3-совместимый API Yandex Cloud.

English version: [README-en.md](README-en.md)

## Возможности

- ✅ **S3-совместимый API** - полная поддержка операций с объектами и бакетами
- ✅ **AWS SDK для PHP** - использует проверенную AWS SDK v3
- ✅ **Поддержка Laravel 8-12** - встроенная интеграция с Service Provider и Facade
- ✅ **Управление бакетами** - создание, удаление, листинг через S3 и REST API
- ✅ **Операции с объектами** - загрузка, скачивание, удаление, копирование
- ✅ **Presigned URLs** - генерация подписанных URL для прямого доступа
- ✅ **IAM Токены** - автоматическое управление и обновление токенов
- ✅ **OAuth интеграция** - получение IAM токенов через OAuth
- ✅ **Управление доступом** - поддержка IAM ролей и прав доступа к бакетам
- ✅ **REST API бакетов** - полное управление бакетами через Yandex Cloud REST API
- ✅ **Управление ролями** - назначение и удаление ролей для бакетов

## Установка

### Через Composer

```bash
composer require tigusigalpa/yandexcloud-s3-php
```

### Требования

- PHP >= 8.0
- Laravel >= 8.0 (опционально)
- AWS SDK for PHP >= 3.200
- Guzzle HTTP >= 7.0

## Быстрый старт

### 1. Получение OAuth токена

Получите OAuth токен по ссылке:
https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb

### 2. Конфигурация для Laravel

Опубликуйте конфигурацию:

```bash
php artisan vendor:publish --tag=yandexcloud-s3-config
```

Добавьте переменные в `.env`:

```env
YANDEX_CLOUD_OAUTH_TOKEN=your_oauth_token_here
YANDEX_CLOUD_BUCKET=your-bucket-name
YANDEX_CLOUD_FOLDER_ID=your-folder-id
YANDEX_CLOUD_ENDPOINT=https://storage.yandexcloud.net
```

### 3. Использование в Laravel

#### Через Facade:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexS3;

// Загрузить файл
YandexS3::putObject('documents/file.pdf', '/local/path/file.pdf', [
    'ContentType' => 'application/pdf',
]);

// Скачать файл
$content = YandexS3::getObject('documents/file.pdf');

// Получить список объектов
$objects = YandexS3::listObjects('documents/');

// Удалить файл
YandexS3::deleteObject('documents/file.pdf');

// Получить signed URL
$url = YandexS3::getObjectUrl('documents/file.pdf', null, 3600);

// Скопировать объект
YandexS3::copyObject('source/file.pdf', 'destination/file.pdf');

// Получить метаданные объекта
$metadata = YandexS3::getObjectMetadata('documents/file.pdf');
echo $metadata->size; // Размер файла
echo $metadata->lastModified; // Дата последнего изменения
```

#### Через Service Container:

```php
use Tigusigalpa\YandexCloudS3\S3Client;

public function upload(S3Client $s3)
{
    $s3->putObject('documents/file.pdf', '/local/path/file.pdf');
}
```

## Использование без Laravel

### Инициализация:

```php
use Tigusigalpa\YandexCloudS3\S3Client;

$s3 = new S3Client(
    oauthToken: 'your_oauth_token',
    bucket: 'your-bucket-name',
    endpoint: 'https://storage.yandexcloud.net', // опционально
    options: [] // опциональные параметры AWS SDK
);
```

### Примеры операций:

```php
// Список объектов
$objects = $s3->listObjects('prefix/', 100);
foreach ($objects as $object) {
    echo $object->key;
    echo $object->size;
    echo $object->lastModified;
}

// Проверка существования
if ($s3->exists('documents/file.pdf')) {
    echo 'Файл существует';
}

// Загрузка
$object = $s3->putObject(
    key: 'documents/file.pdf',
    body: '/local/path/file.pdf', // путь к файлу или содержимое
    options: [
        'ContentType' => 'application/pdf',
        'Metadata' => ['custom-key' => 'custom-value'],
    ]
);
echo $object->etag;

// Скачивание
$content = $s3->getObject('documents/file.pdf');
file_put_contents('/local/path/downloaded.pdf', $content);

// Метаданные
$metadata = $s3->getObjectMetadata('documents/file.pdf');
echo $metadata->size;
echo $metadata->contentType;

// Удаление
$s3->deleteObject('documents/file.pdf');

// Массовое удаление
$result = $s3->deleteObjects([
    'documents/file1.pdf',
    'documents/file2.pdf',
    'documents/file3.pdf',
]);
echo count($result['deleted']); // Количество удаленных

// Копирование
$s3->copyObject(
    sourceKey: 'source/file.pdf',
    destinationKey: 'backup/file.pdf'
);

// Presigned URL
$url = $s3->getObjectUrl('documents/file.pdf', null, 3600); // 1 час

// Смена бакета
$s3->setBucket('another-bucket');
$currentBucket = $s3->getBucket();
```

## Работа с бакетами

```php
// Создание бакета
$s3->createBucket('new-bucket-name');

// Список всех бакетов
$buckets = $s3->listBuckets();
foreach ($buckets as $bucket) {
    echo $bucket->name;
    echo $bucket->createdAt;
}

// Удаление бакета (должен быть пуст)
$s3->deleteBucket('bucket-to-delete');

// Удаление с указанным именем
$s3->deleteBucket();
```

## REST API управление бакетами

Для расширенного управления бакетами используйте `BucketManagementClient`:

### В Laravel через Facade:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexBucketManagement;

// Создать бакет через REST API
$bucket = YandexBucketManagement::createBucket('new-bucket', [
    'defaultStorageClass' => 'STANDARD',
]);

// Получить информацию о бакете
$bucket = YandexBucketManagement::getBucket('my-bucket');
echo $bucket->name;
echo $bucket->folderId;
echo $bucket->createdAt;

// Список бакетов в папке
$buckets = YandexBucketManagement::listBuckets();
foreach ($buckets as $bucket) {
    echo $bucket->name;
}

// Обновить бакет
$bucket = YandexBucketManagement::updateBucket('my-bucket', [
    'maxSize' => 1073741824, // 1GB
]);

// Удалить бакет
YandexBucketManagement::deleteBucket('old-bucket');
```

### Без Laravel:

```php
use Tigusigalpa\YandexCloudS3\BucketManagementClient;

$bucketClient = new BucketManagementClient(
    oauthToken: 'your_oauth_token',
    folderId: 'your_folder_id'
);

$bucket = $bucketClient->createBucket('new-bucket');
```

## Управление ролями и доступом к бакетам

### Назначение ролей:

```php
use Tigusigalpa\YandexCloudS3\Laravel\Facades\YandexBucketManagement;

// Добавить роль пользователю
YandexBucketManagement::addRoleToBucket(
    bucketName: 'my-bucket',
    subjectId: 'user-account-id',
    roleId: 'storage.editor', // или 'storage.viewer', 'storage.admin'
    subjectType: 'userAccount' // или 'serviceAccount'
);

// Удалить роль
YandexBucketManagement::removeRoleFromBucket(
    bucketName: 'my-bucket',
    subjectId: 'user-account-id',
    roleId: 'storage.editor'
);

// Получить список ролей бакета
$bindings = YandexBucketManagement::listAccessBindings('my-bucket');
foreach ($bindings as $binding) {
    echo $binding['roleId'];
    echo $binding['subject']['id'];
}
```

### Расширенное управление ролями:

```php
// Установить роли (перезаписывает все существующие)
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

// Обновить роли (добавить/удалить)
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

### Доступные роли для Object Storage:

- `storage.admin` - полный доступ к бакету
- `storage.editor` - чтение и запись объектов
- `storage.viewer` - только чтение объектов
- `storage.uploader` - только загрузка объектов
- `storage.configViewer` - просмотр конфигурации
- `storage.configurer` - управление конфигурацией

## Управление доступом и IAM

```php
use Tigusigalpa\YandexCloudS3\Auth\S3AuthManager;

$authManager = $s3->getAuthManager();
$oauthManager = $authManager->getOAuthTokenManager();

// Получить информацию о пользователе
$user = $oauthManager->getUserByLogin('username');
echo $user['id']; // Subject ID для использования в ролях

// Получить информацию об облаке
$clouds = $oauthManager->listClouds();
foreach ($clouds as $cloud) {
    echo $cloud['id'];
    echo $cloud['name'];
}

// Получить папки облака
$folders = $oauthManager->listFolders($cloudId);
foreach ($folders as $folder) {
    echo $folder['id'];
    echo $folder['name'];
}
```

## Управление токенами

```php
// Получить текущий IAM токен (автоматически обновляется)
$token = $s3->getAuthManager()->getValidIamToken();

// Получить новый IAM токен
$newToken = $s3->getAuthManager()->getIamToken();

// Переинициализировать клиент (обновить токены)
$s3->refreshToken();
```

## Передача опций AWS SDK

```php
// При инициализации
$s3 = new S3Client(
    oauthToken: 'token',
    bucket: 'bucket',
    options: [
        'use_path_style_endpoint' => true,
        'signature_version' => 'v4',
    ]
);

// Или получить клиент AWS напрямую
$awsClient = $s3->getAwsClient();
$result = $awsClient->getObject([
    'Bucket' => 'my-bucket',
    'Key' => 'my-key',
]);
```

## Обработка ошибок

```php
use Tigusigalpa\YandexCloudS3\Exceptions\{
    YandexCloudS3Exception,
    ApiException,
    AuthenticationException,
};

try {
    $s3->getObject('non-existent-file.pdf');
} catch (ApiException $e) {
    echo 'API ошибка: ' . $e->getMessage();
} catch (AuthenticationException $e) {
    echo 'Ошибка аутентификации: ' . $e->getMessage();
} catch (YandexCloudS3Exception $e) {
    echo 'Общая ошибка: ' . $e->getMessage();
}
```

## Конфигурация Laravel

Файл `config/yandexcloud-s3.php`:

```php
return [
    // OAuth токен Yandex Cloud
    'oauth_token' => env('YANDEX_CLOUD_OAUTH_TOKEN'),
    
    // Бакет по умолчанию
    'bucket' => env('YANDEX_CLOUD_BUCKET'),
    
    // Folder ID для REST API управления бакетами
    'folder_id' => env('YANDEX_CLOUD_FOLDER_ID'),
    
    // Endpoint (по умолчанию storage.yandexcloud.net)
    'endpoint' => env('YANDEX_CLOUD_ENDPOINT', 'https://storage.yandexcloud.net'),
    
    // Регион
    'region' => env('YANDEX_CLOUD_REGION', 'ru-central1'),
    
    // Опции AWS SDK
    'options' => [
        'http' => [
            'timeout' => 30,
            'connect_timeout' => 10,
        ],
    ],
];
```

## Примеры использования

### Загрузка файла через HTTP

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

### Получение списка файлов

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

### Скачивание файла

```php
public function downloadFile($filename)
{
    $content = YandexS3::getObject('documents/' . $filename);
    
    return response($content, 200)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', "attachment; filename='$filename'");
}
```

## Документация и ссылки

- [Yandex Cloud Object Storage](https://yandex.cloud/ru/services/storage)
- [Документация Object Storage](https://yandex.cloud/ru/docs/storage/quickstart/)
- [REST API Документация](https://yandex.cloud/ru/docs/storage/api-ref/Bucket/)
- [S3 API Документация](https://yandex.cloud/ru/docs/storage/s3/api-ref/object)
- [AWS SDK for PHP](https://docs.aws.amazon.com/sdk-for-php/)
- [Управление доступом](https://yandex.cloud/ru/docs/storage/security/)

## Лицензия

MIT License - смотрите файл [LICENSE](LICENSE) для деталей.

## Автор

Igor Sazonov - [GitHub](https://github.com/tigusigalpa)

## Благодарности

Спасибо Yandex Cloud за предоставление S3-совместимого API и AWS за отличный PHP SDK.
