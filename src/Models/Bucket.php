<?php

namespace Tigusigalpa\YandexCloudS3\Models;

/**
 * Represents an S3 bucket
 */
class Bucket
{
    public string $name;
    public ?string $id = null;
    public ?string $createdAt = null;
    public ?array $metadata = null;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->id = $data['id'] ?? null;
        $this->createdAt = $data['created_at'] ?? $data['createdAt'] ?? null;
        $this->metadata = $data['metadata'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'metadata' => $this->metadata,
        ];
    }
}
