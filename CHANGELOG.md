# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-22

### Added

- Initial release of Yandex Cloud S3 PHP SDK
- Complete S3 API support for Yandex Cloud Object Storage
- AWS SDK for PHP v3 integration
- Laravel 8-12 support with Service Provider and Facade
- Full bucket management (create, list, delete) via S3 API
- REST API bucket management client (`BucketManagementClient`)
- Bucket role management (add, remove, list access bindings)
- Support for IAM roles: storage.admin, storage.editor, storage.viewer, etc.
- Object operations (put, get, delete, copy, list)
- Presigned URLs for direct access
- IAM token management with automatic refresh (12-hour validity)
- OAuth token integration
- Authentication and access management
- User and folder management via OAuth
- Comprehensive error handling with custom exceptions
- Unit and feature tests
- Complete documentation with examples in Russian and English
- Support for all major PHP 8.0+ versions
- Two facades: `YandexS3` for S3 operations, `YandexBucketManagement` for REST API
