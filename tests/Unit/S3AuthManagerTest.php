<?php

namespace Tigusigalpa\YandexCloudS3\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudS3\Auth\S3AuthManager;

class S3AuthManagerTest extends TestCase
{
    public function test_throws_exception_when_oauth_token_is_empty()
    {
        $this->expectException(AuthenticationException::class);
        new S3AuthManager('');
    }

    public function test_throws_exception_when_oauth_token_is_null()
    {
        $this->expectException(AuthenticationException::class);
        new S3AuthManager('');
    }

    public function test_can_be_instantiated_with_valid_token()
    {
        $authManager = new S3AuthManager('valid-token-123');
        $this->assertInstanceOf(S3AuthManager::class, $authManager);
    }
}
