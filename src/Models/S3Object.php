<?php

namespace Tigusigalpa\YandexCloudS3\Models;

/**
 * Represents an S3 object (file)
 */
class S3Object
{
    public string $key;
    public string $bucket;
    public ?string $etag = null;
    public ?int $size = null;
    public ?string $lastModified = null;
    public ?string $storageClass = null;
    public ?array $metadata = null;
    public ?string $contentType = null;
    public ?string $versionId = null;

    public function __construct(array $data = [])
    {
        $this->key = $data['key'] ?? $data['Key'] ?? '';
        $this->bucket = $data['bucket'] ?? '';
        $this->etag = $data['etag'] ?? $data['ETag'] ?? null;
        $this->size = $data['size'] ?? $data['Size'] ?? null;
        $this->lastModified = $data['last_modified'] ?? $data['LastModified'] ?? null;
        $this->storageClass = $data['storage_class'] ?? $data['StorageClass'] ?? null;
        $this->metadata = $data['metadata'] ?? $data['Metadata'] ?? null;
        $this->contentType = $data['content_type'] ?? $data['ContentType'] ?? null;
        $this->versionId = $data['version_id'] ?? $data['VersionId'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'bucket' => $this->bucket,
            'etag' => $this->etag,
            'size' => $this->size,
            'last_modified' => $this->lastModified,
            'storage_class' => $this->storageClass,
            'metadata' => $this->metadata,
            'content_type' => $this->contentType,
            'version_id' => $this->versionId,
        ];
    }

    /**
     * Get object path for operations
     */
    public function getPath(): string
    {
        return $this->bucket . '/' . $this->key;
    }
}
