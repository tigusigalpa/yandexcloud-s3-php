<?php

namespace Tigusigalpa\YandexCloudS3\Models;

/**
 * Represents an S3 bucket
 */
class Bucket
{
    public string $name;
    public ?string $id = null;
    public ?string $folderId = null;
    public ?string $createdAt = null;
    public ?string $defaultStorageClass = null;
    public ?int $maxSize = null;
    public ?array $anonymousAccessFlags = null;
    public ?array $metadata = null;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->id = $data['id'] ?? null;
        $this->folderId = $data['folder_id'] ?? $data['folderId'] ?? null;
        $this->createdAt = $data['created_at'] ?? $data['createdAt'] ?? null;
        $this->defaultStorageClass = $data['default_storage_class'] ?? $data['defaultStorageClass'] ?? null;
        $this->maxSize = $data['max_size'] ?? $data['maxSize'] ?? null;
        $this->anonymousAccessFlags = $data['anonymous_access_flags'] ?? $data['anonymousAccessFlags'] ?? null;
        $this->metadata = $data['metadata'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'id' => $this->id,
            'folder_id' => $this->folderId,
            'created_at' => $this->createdAt,
            'default_storage_class' => $this->defaultStorageClass,
            'max_size' => $this->maxSize,
            'anonymous_access_flags' => $this->anonymousAccessFlags,
            'metadata' => $this->metadata,
        ];
    }
}
