<?php

namespace Tigusigalpa\YandexCloudS3\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudS3\Exceptions\YandexCloudS3Exception;
use Tigusigalpa\YandexCloudS3\Exceptions\ApiException;
use Tigusigalpa\YandexCloudS3\Exceptions\AuthenticationException;

class ExceptionsTest extends TestCase
{
    public function test_base_exception_extends_exception()
    {
        $exception = new YandexCloudS3Exception('Test error');
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test error', $exception->getMessage());
    }

    public function test_api_exception_extends_base()
    {
        $exception = new ApiException('API error');
        $this->assertInstanceOf(YandexCloudS3Exception::class, $exception);
    }

    public function test_authentication_exception_extends_base()
    {
        $exception = new AuthenticationException('Auth error');
        $this->assertInstanceOf(YandexCloudS3Exception::class, $exception);
    }
}
