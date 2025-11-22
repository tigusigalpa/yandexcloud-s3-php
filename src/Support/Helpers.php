<?php

namespace Tigusigalpa\YandexCloudS3\Support;

/**
 * Helper utilities for Yandex Cloud S3 operations
 */
class Helpers
{
    /**
     * Generate a unique object key with timestamp
     *
     * @param string $prefix Optional prefix
     * @param string $extension Optional file extension
     * @return string
     */
    public static function generateUniqueKey(string $prefix = '', string $extension = ''): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $key = "{$timestamp}_{$random}";

        if ($prefix) {
            $prefix = rtrim($prefix, '/');
            $key = "{$prefix}/{$key}";
        }

        if ($extension) {
            $extension = ltrim($extension, '.');
            $key = "{$key}.{$extension}";
        }

        return $key;
    }

    /**
     * Get MIME type from file extension
     *
     * @param string $filename
     * @return string Default: application/octet-stream
     */
    public static function getMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'zip' => 'application/zip',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Format bytes to human-readable size
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Validate bucket name according to S3 rules
     *
     * @param string $bucketName
     * @return bool
     */
    public static function isValidBucketName(string $bucketName): bool
    {
        // Bucket names must be between 3 and 63 characters
        if (strlen($bucketName) < 3 || strlen($bucketName) > 63) {
            return false;
        }

        // Bucket names must consist only of lowercase letters, numbers, and hyphens
        if (!preg_match('/^[a-z0-9-]+$/', $bucketName)) {
            return false;
        }

        // Bucket names must begin and end with a letter or number
        if (!preg_match('/^[a-z0-9].*[a-z0-9]$|^[a-z0-9]$/', $bucketName)) {
            return false;
        }

        // Bucket names cannot contain consecutive hyphens
        if (strpos($bucketName, '--') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Validate object key
     *
     * @param string $key
     * @return bool
     */
    public static function isValidObjectKey(string $key): bool
    {
        // Key must not be empty
        if (empty($key)) {
            return false;
        }

        // Key must not exceed 1024 characters
        if (strlen($key) > 1024) {
            return false;
        }

        return true;
    }
}
